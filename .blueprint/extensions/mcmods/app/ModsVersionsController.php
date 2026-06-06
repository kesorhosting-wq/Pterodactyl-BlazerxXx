<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmods;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class ModsVersionsController extends ClientApiController
{
    protected array $httpClient;

    /**
     * Constructs the ModsVersionsController.
     * Initializes HTTP clients with base URIs and headers.
     */
    public function __construct(
        private BlueprintAdminLibrary $blueprint
    ) {
        $apiKey = $this->blueprint->dbGet('mcmods', 'curseforge_api_key', null);
        $this->httpClient = [
            'modrinth' => new Client(['base_uri' => 'https://api.modrinth.com/v2/']),
            'curseforge' => new Client([
                'base_uri' => 'https://api.curseforge.com/v1/',
                'headers' => ['X-API-Key' => $apiKey ?? '']
            ]),
        ];
    }

    /**
     * Fetches mod versions from the specified provider.
     * 
     * @param Request $request The HTTP request containing query parameters.
     * @param Server $server The server associated with the request.
     * 
     * @throws AuthorizationException if the user does not have permission.
     */
    public function index(Request $request, Server $server)
    {
        if (!$request->user()->can(Permission::ACTION_FILE_READ, $server)) {
            throw new AuthorizationException();
        }
        $provider = $request->query('provider');
        $modId = $request->query('modId');

        $url = $this->getUrl($provider, $modId);
        $client = $this->httpClient[$provider];
        
        $response = $client->get($url);

        if ($response->getStatusCode() !== 200) {
            return response()->json(['status' => 'error', 'message' => 'Error fetching Versions'], 503);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $formattedData = $this->formatResponse($provider, $data);

        return response()->json(['data' => $formattedData]);
    }

    /**
     * Constructs the URL for fetching mods versions based on the provider and modId.
     * 
     * @param string $provider The provider.
     * @param string|int $modId The ID of the mod.
     * 
     * @return string The constructed URL for the API request.
     */
    private function getUrl(string $provider, string|int $modId): string
    {
        return match ($provider) {
            'modrinth' => "project/{$modId}/version",
            'curseforge' => "mods/{$modId}/files",
        };
    }

    /**
     * Formats the API response data to a consistent structure.
     * 
     * @param string $provider The provider of mods.
     * @param array $data The raw data from the API response.
     * 
     * @return array The formatted array of mod versions.
     */
    private function formatResponse(string $provider, array $data): array
    {
        return match ($provider) {
            'modrinth' => array_map(fn($version) => [
                'provider' => $provider,
                'versionId' => $version['id'],
                'versionName' => $version['name'],
                'game_versions' => $version['game_versions'],
                'loaders' => $version['loaders'],
                'downloads' => $version['downloads'] > 0 ? $version['downloads'] : null,
            ], $data),
            'curseforge' => array_map(fn($version) => [
                'provider' => $provider,
                'versionId' => $version['id'],
                'versionName' => $version['displayName'],
                'game_versions' => $version['gameVersions'],
                'loaders' => null,
                'downloads' => $version['downloadCount'] > 0 ? $version['downloadCount'] : null,
            ], $data['data']),
        };
    }
}
