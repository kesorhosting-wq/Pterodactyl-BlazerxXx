<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use CURLFile;
use Exception;
use ZipArchive;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Permission;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Modpacks\InstallModpackRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Modpacks\ListModpacksRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ModpackController extends ClientApiController
{
    private const STARTUP_COMMAND = './start.sh';
    private const MARKER_FILE = '.modpack-install';

    public function __construct(
        private DaemonFileRepository $fileRepository,
        private DaemonServerRepository $serverRepository,
        private NodeJWTService $jwtService,
        private SettingsRepositoryInterface $settings
    ) {
        parent::__construct();
    }

    public function search(ListModpacksRequest $request, Server $server): JsonResponse
    {
        $provider = $request->input('provider');
        $query = (string) ($request->input('query') ?? '');
        $page = max(1, (int) $request->input('page', 1));

        if ($provider === 'curseforge' && !$this->curseForgeKey()) {
            return new JsonResponse(['data' => [], 'meta' => ['curseforge_configured' => false]]);
        }

        return new JsonResponse([
            'data' => $provider === 'curseforge' ? $this->searchCurseForge($query, $page) : $this->searchModrinth($query, $page),
            'meta' => [
                'curseforge_configured' => (bool) $this->curseForgeKey(),
                'page' => $page,
                'per_page' => 20,
            ],
        ]);
    }

    public function versions(ListModpacksRequest $request, Server $server): JsonResponse
    {
        $provider = $request->input('provider');
        $project = $request->input('project_id');

        if (!$project) {
            return new JsonResponse(['errors' => [['detail' => 'Select a modpack first.']]], 422);
        }

        if ($provider === 'curseforge' && !$this->curseForgeKey()) {
            return new JsonResponse(['errors' => [['detail' => 'CurseForge requires CURSEFORGE_API_KEY in the panel environment.']]], 422);
        }

        return new JsonResponse([
            'data' => $provider === 'curseforge' ? $this->curseForgeVersions($project) : $this->modrinthVersions($project),
        ]);
    }

    public function install(InstallModpackRequest $request, Server $server): JsonResponse|StreamedResponse
    {
        set_time_limit(0);

        if ($request->boolean('wipe_files') && !$request->user()->can(Permission::ACTION_FILE_DELETE, $server)) {
            return new JsonResponse(['errors' => [['detail' => 'You do not have permission to wipe server files.']]], 403);
        }

        return new StreamedResponse(function () use ($request, $server) {
            $log = fn (string $message, string $type = 'log', array $data = []) => $this->stream($type, $message, $data);
            $tmp = $archive = null;

            try {
                $provider = $request->input('provider');
                $log(sprintf('Preparing %s modpack install...', ucfirst($provider)));
                $tmp = $this->makeTempDirectory();

                $metadata = $provider === 'curseforge'
                    ? $this->prepareCurseForge($request->input('project_id'), $request->input('version_id'), $tmp, $log)
                    : $this->prepareModrinth($request->input('version_id'), $tmp, $log);

                $this->writeRuntimeFiles($server, $tmp, $metadata, $log);
                $archive = $this->zipDirectory($tmp);

                if ($request->boolean('wipe_files')) {
                    $log('Wiping current server files...');
                    $this->wipeServerRoot($server);
                    $log('Current server files were wiped.', 'success');
                }

                $remoteArchive = 'modpack-install.zip';
                $log('Uploading prepared modpack archive to Wings...');
                $this->uploadArchiveToServer($request, $server, $archive, $remoteArchive);
                $log('Extracting modpack archive on Wings...');
                $this->fileRepository->setServer($server)->decompressFile('/', $remoteArchive);
                $this->fileRepository->setServer($server)->deleteFiles('/', [$remoteArchive]);
                $this->fileRepository->setServer($server)->chmodFiles('/', [['file' => 'start.sh', 'mode' => '0755']]);

                $server->forceFill(['startup' => self::STARTUP_COMMAND])->save();
                $this->serverRepository->setServer($server->fresh())->sync();

                $this->fileRepository->setServer($server)->putContent(self::MARKER_FILE, json_encode([
                    'provider' => $provider,
                    'project_id' => $request->input('project_id'),
                    'version_id' => $request->input('version_id'),
                    'name' => $metadata['name'] ?? null,
                    'minecraft' => $metadata['minecraft'] ?? null,
                    'loader' => $metadata['loader'] ?? null,
                    'installed_at' => now()->toIso8601String(),
                ], JSON_PRETTY_PRINT));

                Activity::event('server:modpack.install')
                    ->property('provider', $provider)
                    ->property('project_id', $request->input('project_id'))
                    ->property('version_id', $request->input('version_id'))
                    ->property('wipe_files', $request->boolean('wipe_files'))
                    ->log();

                $log('Modpack installed. Startup was changed to ./start.sh.', 'complete', $metadata);
            } catch (Throwable $exception) {
                $log($exception->getMessage(), 'error');
            } finally {
                if (is_string($archive) && file_exists($archive)) {
                    @unlink($archive);
                }

                if (is_string($tmp) && is_dir($tmp)) {
                    $this->removeDirectory($tmp);
                }
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'application/x-ndjson',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function searchModrinth(string $query, int $page): array
    {
        $response = Http::acceptJson()->timeout(20)->get('https://api.modrinth.com/v2/search', [
            'query' => $query,
            'facets' => '[["project_type:modpack"]]',
            'index' => 'downloads',
            'limit' => 20,
            'offset' => ($page - 1) * 20,
        ]);

        if (!$response->successful()) {
            throw new Exception('Modrinth search failed.');
        }

        return collect($response->json('hits', []))->map(fn (array $item) => [
            'id' => $item['project_id'],
            'slug' => $item['slug'],
            'name' => $item['title'],
            'summary' => $item['description'] ?? '',
            'icon_url' => $item['icon_url'] ?? null,
            'downloads' => $item['downloads'] ?? 0,
            'versions' => $item['versions'] ?? [],
        ])->all();
    }

    private function modrinthVersions(string $project): array
    {
        $response = Http::acceptJson()->timeout(20)->get("https://api.modrinth.com/v2/project/{$project}/version");

        if (!$response->successful()) {
            throw new Exception('Unable to load Modrinth versions.');
        }

        return collect($response->json())->map(fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['name'],
            'version_number' => $item['version_number'],
            'game_versions' => $item['game_versions'] ?? [],
            'loaders' => $item['loaders'] ?? [],
            'date_published' => $item['date_published'] ?? null,
        ])->all();
    }

    private function searchCurseForge(string $query, int $page): array
    {
        $response = $this->curseForge()->get('https://api.curseforge.com/v1/mods/search', [
            'gameId' => 432,
            'classId' => 4471,
            'searchFilter' => $query,
            'sortField' => 6,
            'sortOrder' => 'desc',
            'pageSize' => 20,
            'index' => ($page - 1) * 20,
        ]);

        if (!$response->successful()) {
            throw new Exception('CurseForge search failed.');
        }

        return collect($response->json('data', []))->map(fn (array $item) => [
            'id' => (string) $item['id'],
            'slug' => $item['slug'] ?? (string) $item['id'],
            'name' => $item['name'],
            'summary' => $item['summary'] ?? '',
            'icon_url' => Arr::get($item, 'logo.thumbnailUrl'),
            'downloads' => $item['downloadCount'] ?? 0,
            'versions' => [],
        ])->all();
    }

    private function curseForgeVersions(string $project): array
    {
        $response = $this->curseForge()->get("https://api.curseforge.com/v1/mods/{$project}/files", [
            'pageSize' => 30,
        ]);

        if (!$response->successful()) {
            throw new Exception('Unable to load CurseForge files.');
        }

        return collect($response->json('data', []))->map(fn (array $item) => [
            'id' => (string) $item['id'],
            'name' => $item['displayName'] ?? $item['fileName'],
            'version_number' => $item['fileName'],
            'game_versions' => $item['gameVersions'] ?? [],
            'loaders' => [],
            'date_published' => $item['fileDate'] ?? null,
        ])->all();
    }

    private function prepareModrinth(string $versionId, string $root, callable $log): array
    {
        $version = Http::acceptJson()->timeout(20)->get("https://api.modrinth.com/v2/version/{$versionId}");
        if (!$version->successful()) {
            throw new Exception('Unable to load the selected Modrinth version.');
        }

        $file = collect($version->json('files', []))->firstWhere('primary', true) ?: Arr::first($version->json('files', []));
        $url = $file['url'] ?? null;
        if (!$url) {
            throw new Exception('The selected Modrinth version has no downloadable .mrpack file.');
        }

        $pack = $this->downloadToTemp($url);
        $zip = new ZipArchive();
        if ($zip->open($pack) !== true) {
            throw new Exception('Unable to open Modrinth pack archive.');
        }

        $index = json_decode($zip->getFromName('modrinth.index.json'), true);
        if (!is_array($index)) {
            throw new Exception('The Modrinth pack is missing modrinth.index.json.');
        }

        $this->extractPrefix($zip, 'overrides/', $root, $log);
        $this->extractPrefix($zip, 'server-overrides/', $root, $log);
        $zip->close();
        @unlink($pack);

        foreach ($index['files'] ?? [] as $entry) {
            if (Arr::get($entry, 'env.server') === 'unsupported') {
                continue;
            }

            $path = $this->safeRelativePath($entry['path'] ?? '');
            $download = Arr::first($entry['downloads'] ?? []);
            if (!$path || !$download) {
                continue;
            }

            $log(sprintf('Downloading %s', $path));
            $this->downloadToPath($download, $root . '/' . $path);
        }

        return [
            'name' => $index['name'] ?? $version->json('name'),
            'minecraft' => Arr::get($index, 'dependencies.minecraft'),
            'loader' => $this->loaderFromDependencies($index['dependencies'] ?? []),
            'loader_version' => $this->loaderVersionFromDependencies($index['dependencies'] ?? []),
            'provider' => 'modrinth',
        ];
    }

    private function prepareCurseForge(string $projectId, string $fileId, string $root, callable $log): array
    {
        $file = $this->curseForge()->get("https://api.curseforge.com/v1/mods/{$projectId}/files/{$fileId}");
        if (!$file->successful()) {
            throw new Exception('Unable to load the selected CurseForge file.');
        }

        $data = $file->json('data');
        $downloadId = ($data['serverPackFileId'] ?? null) ?: $fileId;
        $downloadUrl = $this->curseForgeDownloadUrl($projectId, $downloadId);
        $pack = $this->downloadToTemp($downloadUrl);
        $zip = new ZipArchive();
        if ($zip->open($pack) !== true) {
            throw new Exception('Unable to open CurseForge pack archive.');
        }

        $manifest = json_decode($zip->getFromName('manifest.json'), true) ?: [];
        $this->extractAllSafe($zip, $root, $log, ['manifest.json', 'modlist.html']);
        $zip->close();
        @unlink($pack);

        if (($data['serverPackFileId'] ?? null) === null && isset($manifest['files'])) {
            foreach ($manifest['files'] as $entry) {
                $modId = $entry['projectID'] ?? null;
                $modFileId = $entry['fileID'] ?? null;
                if (!$modId || !$modFileId) {
                    continue;
                }

                $modFile = $this->curseForge()->get("https://api.curseforge.com/v1/mods/{$modId}/files/{$modFileId}");
                $filename = $modFile->json('data.fileName');
                $url = $modFile->json('data.downloadUrl') ?: $this->curseForgeDownloadUrl($modId, $modFileId);
                if (!$filename || !$url) {
                    continue;
                }

                $log(sprintf('Downloading mods/%s', $filename));
                $this->downloadToPath($url, $root . '/mods/' . $filename);
            }
        }

        $loader = $this->loaderFromCurseManifest($manifest);

        return [
            'name' => $manifest['name'] ?? $data['displayName'] ?? $data['fileName'],
            'minecraft' => Arr::get($manifest, 'minecraft.version'),
            'loader' => $loader[0],
            'loader_version' => $loader[1],
            'provider' => 'curseforge',
        ];
    }

    private function writeRuntimeFiles(Server $server, string $root, array $metadata, callable $log): void
    {
        file_put_contents($root . '/eula.txt', "eula=true\n");
        $loader = $metadata['loader'] ?? null;
        $minecraft = $metadata['minecraft'] ?? null;
        $loaderVersion = $metadata['loader_version'] ?? null;
        $memory = max(128, (int) $server->memory);

        if ($loader === 'fabric' && $minecraft && $loaderVersion) {
            $installer = Http::acceptJson()->timeout(20)->get('https://meta.fabricmc.net/v2/versions/installer')->json('0.version');
            $url = sprintf('https://meta.fabricmc.net/v2/versions/loader/%s/%s/%s/server/jar', $minecraft, $loaderVersion, $installer);
            $log('Downloading Fabric server launcher.');
            $this->downloadToPath($url, $root . '/server.jar');
            $script = "#!/bin/sh\njava -Xms128M -Xmx{$memory}M -jar server.jar nogui\n";
        } elseif ($loader === 'forge' && $minecraft && $loaderVersion) {
            $installer = sprintf('forge-%s-%s-installer.jar', $minecraft, $loaderVersion);
            $this->downloadToPath(sprintf('https://maven.minecraftforge.net/net/minecraftforge/forge/%s-%s/%s', $minecraft, $loaderVersion, $installer), $root . '/' . $installer);
            $script = "#!/bin/sh\nif [ ! -f libraries/net/minecraftforge/forge/{$minecraft}-{$loaderVersion}/unix_args.txt ]; then java -jar {$installer} --installServer; fi\njava -Xms128M -Xmx{$memory}M @libraries/net/minecraftforge/forge/{$minecraft}-{$loaderVersion}/unix_args.txt nogui\n";
        } elseif ($loader === 'neoforge' && $loaderVersion) {
            $installer = sprintf('neoforge-%s-installer.jar', $loaderVersion);
            $this->downloadToPath(sprintf('https://maven.neoforged.net/releases/net/neoforged/neoforge/%s/%s', $loaderVersion, $installer), $root . '/' . $installer);
            $script = "#!/bin/sh\nif [ ! -f libraries/net/neoforged/neoforge/{$loaderVersion}/unix_args.txt ]; then java -jar {$installer} --installServer; fi\njava -Xms128M -Xmx{$memory}M @libraries/net/neoforged/neoforge/{$loaderVersion}/unix_args.txt nogui\n";
        } else {
            $script = "#!/bin/sh\necho 'This modpack was installed, but no supported server loader was detected.'\necho 'Upload or configure a server jar, then update start.sh.'\nexit 1\n";
        }

        file_put_contents($root . '/start.sh', $script);
    }

    private function loaderFromDependencies(array $deps): ?string
    {
        foreach (['fabric-loader' => 'fabric', 'forge' => 'forge', 'neoforge' => 'neoforge', 'quilt-loader' => 'quilt'] as $key => $loader) {
            if (isset($deps[$key])) {
                return $loader;
            }
        }

        return null;
    }

    private function loaderVersionFromDependencies(array $deps): ?string
    {
        foreach (['fabric-loader', 'forge', 'neoforge', 'quilt-loader'] as $key) {
            if (isset($deps[$key])) {
                return $deps[$key];
            }
        }

        return null;
    }

    private function loaderFromCurseManifest(array $manifest): array
    {
        $id = Arr::get($manifest, 'minecraft.modLoaders.0.id', '');
        if (str_contains($id, '-')) {
            [$loader, $version] = explode('-', $id, 2);
            return [$loader === 'neoforge' ? 'neoforge' : $loader, $version];
        }

        return [null, null];
    }

    private function curseForgeDownloadUrl(string $projectId, string $fileId): string
    {
        $response = $this->curseForge()->get("https://api.curseforge.com/v1/mods/{$projectId}/files/{$fileId}/download-url");
        $url = $response->json('data');
        if (!$response->successful() || !$url) {
            throw new Exception('CurseForge did not return a download URL. Check the API key and file availability.');
        }

        return $url;
    }

    private function curseForge()
    {
        return Http::acceptJson()->withHeaders(['x-api-key' => $this->curseForgeKey()])->timeout(30);
    }

    private function curseForgeKey(): ?string
    {
        return $this->settings->get('settings::modpacks:curseforge_api_key') ?: env('CURSEFORGE_API_KEY') ?: config('services.curseforge.key');
    }

    private function downloadToTemp(string $url): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'modpack-download-');
        if (!$tmp) {
            throw new Exception('Unable to create a temporary download file.');
        }

        $this->downloadToPath($url, $tmp);

        return $tmp;
    }

    private function downloadToPath(string $url, string $path): void
    {
        @mkdir(dirname($path), 0755, true);
        $response = Http::withHeaders(['User-Agent' => 'Pterodactyl-Modpack-Installer'])->timeout(300)->sink($path)->get($url);

        if (!$response->successful()) {
            throw new Exception(sprintf('Download failed with HTTP %s: %s', $response->status(), basename($path)));
        }
    }

    private function extractPrefix(ZipArchive $zip, string $prefix, string $root, callable $log): void
    {
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);
            if (!str_starts_with($name, $prefix) || str_ends_with($name, '/')) {
                continue;
            }

            $relative = $this->safeRelativePath(substr($name, strlen($prefix)));
            if (!$relative) {
                continue;
            }

            $log(sprintf('Extracting %s', $relative));
            @mkdir(dirname($root . '/' . $relative), 0755, true);
            file_put_contents($root . '/' . $relative, $zip->getFromIndex($i));
        }
    }

    private function extractAllSafe(ZipArchive $zip, string $root, callable $log, array $skip = []): void
    {
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '/') || in_array($name, $skip, true)) {
                continue;
            }

            $relative = $this->safeRelativePath(preg_replace('#^overrides/#', '', $name));
            if (!$relative) {
                continue;
            }

            $log(sprintf('Extracting %s', $relative));
            @mkdir(dirname($root . '/' . $relative), 0755, true);
            file_put_contents($root . '/' . $relative, $zip->getFromIndex($i));
        }
    }

    private function safeRelativePath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '' || str_contains($path, '..') || preg_match('#^[A-Za-z]:#', $path)) {
            return null;
        }

        return $path;
    }

    private function makeTempDirectory(): string
    {
        $path = sys_get_temp_dir() . '/modpack-install-' . bin2hex(random_bytes(8));
        if (!mkdir($path, 0755, true) && !is_dir($path)) {
            throw new Exception('Unable to create temporary install directory.');
        }

        return $path;
    }

    private function zipDirectory(string $directory): string
    {
        $archive = tempnam(sys_get_temp_dir(), 'modpack-archive-');
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            throw new Exception('Unable to create modpack archive.');
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $zip->addFile($file->getPathname(), substr($file->getPathname(), strlen($directory) + 1));
            }
        }

        $zip->close();

        return $archive;
    }

    private function uploadArchiveToServer(InstallModpackRequest $request, Server $server, string $localArchive, string $archive): void
    {
        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
            ->setUser($request->user())
            ->setClaims(['server_uuid' => $server->uuid])
            ->handle($server->node, $request->user()->id . $server->uuid);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => sprintf('%s/upload/file?token=%s&directory=%s', $server->node->getConnectionAddress(), $token->toString(), urlencode('/')),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['files' => new CURLFile($localArchive, 'application/zip', $archive)],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new Exception($error ?: sprintf('Wings upload returned HTTP %s.', $status));
        }
    }

    private function wipeServerRoot(Server $server): void
    {
        $files = collect($this->fileRepository->setServer($server)->getDirectory('/'))->pluck('name')->filter()->values()->all();
        if ($files !== []) {
            $this->fileRepository->setServer($server)->deleteFiles('/', $files);
        }
    }

    private function removeDirectory(string $path): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }

        @rmdir($path);
    }

    private function stream(string $type, string $message, array $data = []): void
    {
        echo json_encode(['type' => $type, 'message' => $message, 'data' => $data, 'time' => now()->toIso8601String()]) . "\n";
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        flush();
    }
}
