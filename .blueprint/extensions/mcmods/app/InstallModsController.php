<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmods;

use GuzzleHttp\Client;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class InstallModsController extends ClientApiController
{
    protected array $httpClient;
    private string $directory = '/mods';
    private DaemonFileRepository $daemonFileRepository;

    /**
     * Constructs the InstallModsController.
     * Initializes HTTP clients with base URIs and headers.
     */
    public function __construct(
        private BlueprintAdminLibrary $blueprint,
        private NodeJWTService $jwtService,
        DaemonFileRepository $daemonFileRepository
    ) {
        $apiKey = $this->blueprint->dbGet('mcmods', 'curseforge_api_key', null);
        $this->httpClient = [
            'modrinth' => new Client(['base_uri' => 'https://api.modrinth.com/v2/']),
            'curseforge' => new Client([
                'base_uri' => 'https://api.curseforge.com/v1/',
                'headers' => ['X-API-Key' => $apiKey ?? '']
            ]),
        ];
        $this->daemonFileRepository = $daemonFileRepository;
    }

    /**
     * Handles the installation of a mod by fetching the mod data and downloading it.
     *
     * @param Request $request The HTTP request containing mod information.
     * @param Server $server The server where the mod will be installed.
     * 
     * @throws AuthorizationException if the user does not have permission.
     */
    public function index(Request $request, Server $server)
    {
        if (!$request->user()->can(Permission::ACTION_FILE_CREATE, $server)) {
            throw new AuthorizationException();
        }
        $provider = $request->input('provider');
        $modId = $request->input('modId');
        $versionId = $request->input('versionId');

        $data = $this->fetchMod($provider, $modId, $versionId);
        if ($data['status'] === 'error') {
            return response()->json(['status' => 'error', 'message' => 'An error occured while installing the mod.'], 503);
        }

        $modFileUrl = $data['modFileUrl'];
        $this->daemonFileRepository->setServer($server)->pull(
            $modFileUrl,
            $this->directory,
            [
                'use_header' => true,
                'foreground' => true,
            ]
        );
        return response()->json(['status' => 'success', 'message' => 'Mod installed successfully']);
    }

    /**
     * Retrieves the mod file URL based on provider, modId, and versionId.
     *
     * @param string $provider The mod provider provider.
     * @param string $modId The modId.
     * @param string|null $versionId The version ID of the mod.
     * 
     * @return array The mod file URL and name or an error message.
     */
    private function fetchMod(string $provider, ?string $modId, ?string $versionId): array
    {
        $data = match ($provider) {
            'modrinth' => $this->fetchModrinthModData($modId, $versionId),
            'curseforge' => $this->fetchCurseForgeModData($modId, $versionId),
            default => throw new \InvalidArgumentException('Unsupported mod provider'),
        };

        return [
            'status' => 'success',
            'modFileUrl' => $data['url'],
            'modName' => $data['name'],
        ];
    }

    /**
     * Fetches Modrinth mod data based on modId and versionId.
     *
     * @param string $modId The ID of the mod.
     * @param string|null $versionId The specific version ID of the mod.
     * 
     * @return array Mod file details including URL and filename.
     */
    private function fetchModrinthModData(?string $modId, ?string $versionId): array
    {
        $client = $this->httpClient['modrinth'];
        $response = $client->get($versionId ? "version/{$versionId}" : "project/{$modId}/version");
        $data = json_decode($response->getBody()->getContents(), true);
        $modFile = $versionId ? $data['files'][0] : $data[0]['files'][0];

        return ['url' => $modFile['url'], 'name' => $modFile['filename']];
    }

    /**
     * Fetches CurseForge mod data based on modId and versionId.
     *
     * @param string $modId The ID of the mod.
     * @param string|null $versionId The specific version ID of the mod.
     * 
     * @return array mod file details including URL and filename.
     */
    private function fetchCurseForgeModData(?string $modId, ?string $versionId): array
    {
        $client = $this->httpClient['curseforge'];
        $response = $client->get($versionId ? "mods/{$modId}/files/{$versionId}" : "mods/{$modId}/files");
        $data = json_decode($response->getBody()->getContents(), true);
        $modFile = $versionId ? $data['data'] : $data['data'][0];

        return [
            'url' => str_replace("edge", "mediafiles", $modFile['downloadUrl']),
            'name' => $modFile['fileName'],
        ];
    }
}
