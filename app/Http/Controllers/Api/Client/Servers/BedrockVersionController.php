<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\BedrockVersions\InstallBedrockVersionRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\BedrockVersions\ListBedrockVersionsRequest;
use Throwable;

class BedrockVersionController extends ClientApiController
{
    private const RAW_BRANCHES = ['v2', 'main', 'master'];
    private const RAW_BASE_URL = 'https://raw.githubusercontent.com/EndstoneMC/bedrock-server-data/%s/%s';
    private const VERSION_MARKER = '.bedrock-version';
    private const STARTUP_COMMAND = './bedrock_server';

    public function __construct(
        private DaemonFileRepository $fileRepository,
        private DaemonServerRepository $serverRepository,
        private NodeJWTService $jwtService
    ) {
        parent::__construct();
    }

    public function index(ListBedrockVersionsRequest $request, Server $server): JsonResponse
    {
        $versions = $this->versions();

        return new JsonResponse([
            'data' => $this->formatVersions($versions),
            'meta' => [
                'current' => $this->currentVersion($server),
                'source' => 'EndstoneMC/bedrock-server-data',
            ],
        ]);
    }

    public function install(InstallBedrockVersionRequest $request, Server $server): JsonResponse
    {
        set_time_limit(600);

        $channel = $request->input('channel');
        $version = $request->input('version');
        $versions = $this->versions();

        if (!in_array($version, Arr::get($versions, "{$channel}.versions", []), true)) {
            return new JsonResponse(['errors' => [['detail' => 'Unknown Bedrock server version.']]], 422);
        }

        $metadata = $this->metadata($channel, $version);
        $downloadUrl = Arr::get($metadata, 'binary.linux.url');

        if (!$downloadUrl) {
            return new JsonResponse(['errors' => [['detail' => 'No Linux Bedrock server download is available.']]], 422);
        }

        $archive = sprintf('bedrock-server-%s.zip', preg_replace('/[^A-Za-z0-9._-]/', '-', $version));
        $localArchive = null;

        try {
            $localArchive = $this->downloadArchive($downloadUrl);
        } catch (Throwable) {
            return $this->installError('The panel could not download the Bedrock archive from minecraft.net.');
        }

        try {
            if ($request->boolean('wipe_files')) {
                $this->wipeServerRoot($server);
            } else {
                $this->fileRepository->setServer($server)->deleteFiles('/', [$archive]);
            }
        } catch (Throwable) {
            // Ignore missing archive cleanup when the user is not wiping files.
            if ($request->boolean('wipe_files')) {
                if (is_string($localArchive) && file_exists($localArchive)) {
                    unlink($localArchive);
                }

                return $this->installError('Wings could not delete the existing server files.');
            }
        }

        try {
            $this->uploadArchiveToServer($request, $server, $localArchive, $archive);
        } catch (Throwable) {
            return $this->installError('The panel downloaded the archive, but could not upload it to Wings.');
        } finally {
            if (is_string($localArchive) && file_exists($localArchive)) {
                unlink($localArchive);
            }
        }

        try {
            $this->fileRepository->setServer($server)->decompressFile('/', $archive);
        } catch (Throwable) {
            return $this->installError('Wings downloaded the archive, but could not extract it.');
        }

        try {
            $this->fileRepository->setServer($server)->deleteFiles('/', [$archive]);
            $this->fileRepository->setServer($server)->putContent(self::VERSION_MARKER, json_encode([
                'channel' => $channel,
                'version' => $version,
                'installed_at' => now()->toIso8601String(),
                'source' => 'EndstoneMC/bedrock-server-data',
            ], JSON_PRETTY_PRINT));
        } catch (Throwable) {
            return $this->installError('Bedrock was extracted, but the install marker or cleanup step failed.');
        }

        try {
            if ($server->startup !== self::STARTUP_COMMAND) {
                $server->forceFill(['startup' => self::STARTUP_COMMAND])->save();
                $this->serverRepository->setServer($server->fresh())->sync();
            }
        } catch (Throwable) {
            return $this->installError('Bedrock was installed, but the startup command could not be changed to ./bedrock_server.');
        }

        Activity::event('server:bedrock-version.install')
            ->property('channel', $channel)
            ->property('version', $version)
            ->property('wipe_files', $request->boolean('wipe_files'))
            ->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function versions(): array
    {
        return Cache::remember('bedrock-server-data:versions', now()->addMinutes(30), function () {
            return $this->fetchJson('versions.json');
        });
    }

    private function metadata(string $channel, string $version): array
    {
        return Cache::remember("bedrock-server-data:metadata:{$channel}:{$version}", now()->addHours(6), function () use ($channel, $version) {
            return $this->fetchJson(sprintf('%s/%s/metadata.json', $channel, rawurlencode($version)));
        });
    }

    private function fetchJson(string $path): array
    {
        foreach (self::RAW_BRANCHES as $branch) {
            try {
                $response = Http::acceptJson()->timeout(15)->get(sprintf(self::RAW_BASE_URL, $branch, $path));
            } catch (Exception) {
                continue;
            }

            if ($response->successful() && is_array($response->json())) {
                return $response->json();
            }
        }

        abort(502, 'Unable to load Bedrock server version data.');
    }

    private function formatVersions(array $versions): array
    {
        return collect(['release', 'preview'])
            ->flatMap(function (string $channel) use ($versions) {
                $latest = Arr::get($versions, "{$channel}.latest");

                return collect(Arr::get($versions, "{$channel}.versions", []))
                    ->map(fn (string $version) => [
                        'channel' => $channel,
                        'version' => $version,
                        'latest' => $version === $latest,
                    ]);
            })
            ->values()
            ->all();
    }

    private function currentVersion(Server $server): ?array
    {
        try {
            $contents = $this->fileRepository->setServer($server)->getContent(self::VERSION_MARKER);
            $decoded = json_decode($contents, true);

            return is_array($decoded) ? $decoded : null;
        } catch (Exception) {
            return null;
        }
    }

    private function wipeServerRoot(Server $server): void
    {
        $files = collect($this->fileRepository->setServer($server)->getDirectory('/'))
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        if ($files !== []) {
            $this->fileRepository->setServer($server)->deleteFiles('/', $files);
        }
    }

    private function downloadArchive(string $url): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bedrock-server-');

        if (!$tmp) {
            throw new Exception('Unable to create temporary file.');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0',
        ])
            ->timeout(300)
            ->sink($tmp)
            ->get($url);

        if (!$response->successful()) {
            throw new Exception(sprintf('minecraft.net returned HTTP %s.', $response->status()));
        }

        return $tmp;
    }

    private function uploadArchiveToServer(InstallBedrockVersionRequest $request, Server $server, string $localArchive, string $archive): void
    {
        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
            ->setUser($request->user())
            ->setClaims(['server_uuid' => $server->uuid])
            ->handle($server->node, $request->user()->id . $server->uuid);

        $uploadUrl = sprintf(
            '%s/upload/file?token=%s&directory=%s',
            $server->node->getConnectionAddress(),
            $token->toString(),
            urlencode('/')
        );

        $curl = curl_init();

        if (!$curl) {
            throw new Exception('Unable to initialize file upload.');
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $uploadUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'files' => new \CURLFile($localArchive, 'application/zip', $archive),
            ],
            CURLOPT_HTTPHEADER => [
                'Accept: application/json, text/plain, */*',
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new Exception($error ?: sprintf('Wings upload returned HTTP %s.', $status));
        }
    }

    private function installError(string $detail): JsonResponse
    {
        return new JsonResponse(['errors' => [['detail' => $detail]]], 502);
    }
}
