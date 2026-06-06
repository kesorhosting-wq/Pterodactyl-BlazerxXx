<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Exception;
use CURLFile;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Permission;
use Pterodactyl\Facades\Activity;
use phpseclib3\Net\SFTP;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Files\ImportServerFilesRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Files\VerifyServerImportRequest;

class ServerImportController extends ClientApiController
{
    public function __construct(
        private NodeJWTService $jwtService,
        private DaemonFileRepository $fileRepository
    ) {
        parent::__construct();
    }

    public function verify(VerifyServerImportRequest $request, Server $server): JsonResponse
    {
        try {
            [$protocol, $host, $port] = $this->parseEndpoint($request->input('host'), $request->input('protocol'), (int) $request->input('port'));
            $path = $this->normalizeRemotePath($request->input('path') ?: '/');
            $listing = $protocol === 'ftp'
                ? $this->listFtpDirectory($this->connectFtp($host, $port, $request->input('username'), $request->input('password')), $path)
                : $this->listSftpDirectory($this->connectSftp($host, $port, $request->input('username'), $request->input('password')), $path);
        } catch (Exception $exception) {
            return $this->importError($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'data' => [
                'path' => $path,
                'entries' => count($listing),
            ],
        ]);
    }

    public function import(ImportServerFilesRequest $request, Server $server): JsonResponse|StreamedResponse
    {
        set_time_limit(0);

        if ($request->boolean('wipe_files') && !$request->user()->can(Permission::ACTION_FILE_DELETE, $server)) {
            return $this->importError('You do not have permission to delete files on this server.', Response::HTTP_FORBIDDEN);
        }

        try {
            [$protocol, $host, $port] = $this->parseEndpoint($request->input('host'), $request->input('protocol'), (int) $request->input('port'));
            $source = $this->normalizeRemotePath($request->input('path') ?: '/');
        } catch (Exception $exception) {
            return $this->importError($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new StreamedResponse(function () use ($request, $server, $protocol, $host, $port, $source) {
            $files = 0;
            $directories = 0;

            $log = function (string $message, string $type = 'log', array $data = []): void {
                $this->streamImportEvent($type, $message, $data);
            };

            try {
                $log(sprintf('Connecting to %s://%s:%s...', $protocol, $host, $port));

                if ($protocol === 'ftp') {
                    $connection = $this->connectFtp($host, $port, $request->input('username'), $request->input('password'));
                    $this->listFtpDirectory($connection, $source);
                    $log(sprintf('Connected. Source directory: %s', $source), 'success');
                } else {
                    $connection = $this->connectSftp($host, $port, $request->input('username'), $request->input('password'));
                    $this->listSftpDirectory($connection, $source);
                    $log(sprintf('Connected. Source directory: %s', $source), 'success');
                }

                if ($request->boolean('wipe_files')) {
                    $log('Wiping current server files...');
                    $this->wipeServerRoot($server);
                    $log('Current server files were wiped.', 'success');
                }

                $log('Starting transfer...');

                if ($protocol === 'ftp') {
                    $this->importFtpDirectory($request, $server, $connection, $source, '/', $files, $directories, $log);
                } else {
                    $this->importSftpDirectory($request, $server, $connection, $source, '/', $files, $directories, $log);
                }

                Activity::event('server:file.import')
                    ->property('source_protocol', $protocol)
                    ->property('source_host', $host)
                    ->property('source_path', $source)
                    ->property('files', $files)
                    ->property('directories', $directories)
                    ->log();

                $log(
                    sprintf('Import complete. %s files and %s directories transferred.', $files, $directories),
                    'complete',
                    [
                        'files' => $files,
                        'directories' => $directories,
                    ]
                );
            } catch (Exception $exception) {
                $log($exception->getMessage(), 'error', [
                    'files' => $files,
                    'directories' => $directories,
                ]);
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'application/x-ndjson',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function streamImportEvent(string $type, string $message, array $data = []): void
    {
        echo json_encode([
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'time' => CarbonImmutable::now()->toIso8601String(),
        ]) . "\n";

        if (ob_get_level() > 0) {
            @ob_flush();
        }

        flush();
    }

    private function connectSftp(string $host, int $port, string $username, string $password): SFTP
    {
        $sftp = new SFTP($host, $port, 15);

        if (!$sftp->login($username, $password)) {
            $message = $sftp->getLastError();

            if (!$message && !$this->canOpenTcpConnection($host, $port)) {
                $message = sprintf('Could not reach %s:%s from the panel server.', $host, $port);
            }

            $message = $message ?: 'The source server rejected those SFTP credentials.';

            throw new Exception($message);
        }

        return $sftp;
    }

    private function connectFtp(string $host, int $port, string $username, string $password)
    {
        $ftp = @ftp_connect($host, $port, 15);
        if (!$ftp) {
            throw new Exception(sprintf('Could not reach %s:%s from the panel server.', $host, $port));
        }

        if (!@ftp_login($ftp, $username, $password)) {
            throw new Exception('The source server rejected those FTP credentials.');
        }

        @ftp_pasv($ftp, true);

        return $ftp;
    }

    private function canOpenTcpConnection(string $host, int $port): bool
    {
        $socket = @fsockopen($host, $port, $errno, $error, 10);
        if (!$socket) {
            return false;
        }

        fclose($socket);

        return true;
    }

    private function listSftpDirectory(SFTP $sftp, string $path): array
    {
        if (!$sftp->is_dir($path)) {
            throw new Exception('The selected source path is not a directory.');
        }

        $listing = $sftp->rawlist($path);
        if (!is_array($listing)) {
            throw new Exception('Unable to read the selected source directory.');
        }

        return array_values(array_filter(array_keys($listing), fn (string $name) => !in_array($name, ['.', '..'], true)));
    }

    private function listFtpDirectory($ftp, string $path): array
    {
        $entries = @ftp_rawlist($ftp, $path);
        if (!is_array($entries)) {
            throw new Exception('Unable to read the selected source directory.');
        }

        $items = [];
        foreach ($entries as $entry) {
            $parts = preg_split('/\s+/', $entry, 9);
            if (!$parts || count($parts) < 9 || in_array($parts[8], ['.', '..'], true)) {
                continue;
            }

            $items[] = [
                'name' => $parts[8],
                'type' => str_starts_with($parts[0], 'd') ? 'directory' : (str_starts_with($parts[0], '-') ? 'file' : 'other'),
            ];
        }

        return $items;
    }

    private function importSftpDirectory(
        ImportServerFilesRequest $request,
        Server $server,
        SFTP $sftp,
        string $remoteDirectory,
        string $destinationDirectory,
        int &$files,
        int &$directories,
        ?callable $log = null
    ): void {
        foreach ($this->listSftpDirectory($sftp, $remoteDirectory) as $name) {
            $remotePath = $this->joinRemotePath($remoteDirectory, $name);

            if ($sftp->is_dir($remotePath)) {
                $log && $log(sprintf('Creating directory %s', $this->joinServerPath($destinationDirectory, $name)));
                $this->ensureServerDirectory($server, $destinationDirectory, $name);
                ++$directories;

                $this->importSftpDirectory(
                    $request,
                    $server,
                    $sftp,
                    $remotePath,
                    $this->joinServerPath($destinationDirectory, $name),
                    $files,
                    $directories,
                    $log
                );

                continue;
            }

            if (!$sftp->is_file($remotePath)) {
                continue;
            }

            $localFile = tempnam(sys_get_temp_dir(), 'server-import-');
            if (!$localFile) {
                throw new Exception('Unable to create a temporary file for the import.');
            }

            try {
                $log && $log(sprintf('Transferring %s', $remotePath));
                if (!$sftp->get($remotePath, $localFile)) {
                    throw new Exception(sprintf('Unable to download %s from the source server.', $remotePath));
                }

                $this->uploadLocalFile($request, $server, $localFile, $name, $destinationDirectory);
                ++$files;
                $log && $log(sprintf('Uploaded %s', $this->joinServerPath($destinationDirectory, $name)), 'success');
            } finally {
                @unlink($localFile);
            }
        }
    }

    private function importFtpDirectory(
        ImportServerFilesRequest $request,
        Server $server,
        $ftp,
        string $remoteDirectory,
        string $destinationDirectory,
        int &$files,
        int &$directories,
        ?callable $log = null
    ): void {
        foreach ($this->listFtpDirectory($ftp, $remoteDirectory) as $item) {
            $remotePath = $this->joinRemotePath($remoteDirectory, $item['name']);

            if ($item['type'] === 'directory') {
                $log && $log(sprintf('Creating directory %s', $this->joinServerPath($destinationDirectory, $item['name'])));
                $this->ensureServerDirectory($server, $destinationDirectory, $item['name']);
                ++$directories;

                $this->importFtpDirectory(
                    $request,
                    $server,
                    $ftp,
                    $remotePath,
                    $this->joinServerPath($destinationDirectory, $item['name']),
                    $files,
                    $directories,
                    $log
                );

                continue;
            }

            if ($item['type'] !== 'file') {
                continue;
            }

            $localFile = tempnam(sys_get_temp_dir(), 'server-import-');
            if (!$localFile) {
                throw new Exception('Unable to create a temporary file for the import.');
            }

            try {
                $log && $log(sprintf('Transferring %s', $remotePath));
                if (!@ftp_get($ftp, $localFile, $remotePath, FTP_BINARY)) {
                    throw new Exception(sprintf('Unable to download %s from the source server.', $remotePath));
                }

                $this->uploadLocalFile($request, $server, $localFile, $item['name'], $destinationDirectory);
                ++$files;
                $log && $log(sprintf('Uploaded %s', $this->joinServerPath($destinationDirectory, $item['name'])), 'success');
            } finally {
                @unlink($localFile);
            }
        }
    }

    private function ensureServerDirectory(Server $server, string $parent, string $name): void
    {
        try {
            $this->fileRepository->setServer($server)->createDirectory($name, $parent);
        } catch (Exception) {
            // Existing directories are fine when importing without wiping first.
        }
    }

    private function uploadLocalFile(ImportServerFilesRequest $request, Server $server, string $localFile, string $filename, string $directory): void
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
            urlencode($directory)
        );

        $handle = curl_init();
        if (!$handle) {
            throw new Exception('Unable to initialize the Wings upload.');
        }

        curl_setopt_array($handle, [
            CURLOPT_URL => $uploadUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'files' => new CURLFile($localFile, 'application/octet-stream', $filename),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 300,
        ]);

        $response = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $error = curl_error($handle);

        curl_close($handle);

        if ($response === false || $status < 200 || $status >= 300) {
            throw new Exception($error ?: sprintf('Wings upload returned HTTP %s while importing %s.', $status, $filename));
        }
    }

    private function wipeServerRoot(Server $server): void
    {
        $items = $this->fileRepository->setServer($server)->getDirectory('/');
        $files = array_values(array_filter(array_map(fn (array $item) => $item['name'] ?? null, $items)));

        if (count($files) > 0) {
            $this->fileRepository->setServer($server)->deleteFiles('/', $files);
        }
    }

    private function normalizeRemotePath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }

        $path = '/' . ltrim($path, '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }

    private function joinRemotePath(string $parent, string $name): string
    {
        return rtrim($parent, '/') . '/' . ltrim($name, '/');
    }

    private function joinServerPath(string $parent, string $name): string
    {
        $path = rtrim($parent, '/') . '/' . trim($name, '/');

        return $path === '' ? '/' : $path;
    }

    private function parseEndpoint(string $host, string $protocol = 'sftp', ?int $fallbackPort = null): array
    {
        $protocol = strtolower($protocol);
        $endpoint = trim($host);
        if ($endpoint === '') {
            throw new Exception('Enter a valid source server host.');
        }

        if (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $endpoint)) {
            $endpoint = $protocol . '://' . $endpoint;
        }

        $parts = parse_url($endpoint);
        $parsedProtocol = strtolower($parts['scheme'] ?? $protocol);
        $parsedHost = $parts['host'] ?? null;
        $parsedPort = $parts['port'] ?? null;

        if (!in_array($parsedProtocol, ['sftp', 'ftp'], true)) {
            throw new Exception('Only SFTP and FTP imports are supported.');
        }

        if (!$parsedHost) {
            throw new Exception('Enter a valid source server host.');
        }

        $defaultPort = $parsedProtocol === 'ftp' ? 21 : 22;

        return [$parsedProtocol, $parsedHost, $parsedPort ?: ($fallbackPort ?: $defaultPort)];
    }

    private function importError(string $detail, int $status): JsonResponse
    {
        return new JsonResponse([
            'errors' => [
                [
                    'code' => 'ServerImportException',
                    'status' => (string) $status,
                    'detail' => $detail,
                ],
            ],
        ], $status);
    }
}
