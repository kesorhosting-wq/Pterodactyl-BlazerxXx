<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmods;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class ModsManagerController extends ClientApiController
{
    protected array $httpClient;
    
    /**
     * Constructs the ModsManagerController.
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
     * Fetches a list of mods based on various filters.
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
        $provider = $request->query('provider', 'modrinth');
        $page = $request->query('page', 1);
        $pageSize = $request->query('page_size', 6);
        $searchQuery = $request->query('search_query', '');
        $loader = $request->query('loader', '');
        $sortBy = $request->query('sort_by', '');
        $minecraftVersion = $request->query('minecraft_version', '');

        $url = $this->getUrl($provider, $page, $pageSize, $searchQuery, $loader, $sortBy, $minecraftVersion);
        $client = $this->httpClient[$provider];

        try {
            $response = $client->get($url);
            if ($response->getStatusCode() !== 200) {
                return response()->json(['status' => 'error', 'message' => 'Error fetching Mods'], 503);
            }

            $data = json_decode($response->getBody()->getContents(), true);
            $pagination = $this->getPagination($provider, $data, $page, $pageSize);
            $formattedData = $this->formatResponse($provider, $data);

            return response()->json([
                'data' => $formattedData,
                'pagination' => $pagination
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error fetching data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Constructs the URL for API requests based on parameters.
     *
     * @param string $provider The provider for mods.
     * @param int $page The current page.
     * @param int $pageSize The number of items per page.
     * @param string $searchQuery The search query.
     * @param string $loader The server loader.
     * @param string $sortBy The sort order.
     * @param string $minecraftVersion The Minecraft version filter.
     * 
     * @return string The constructed URL for the API request.
     */
    private function getUrl(
        string $provider,
        int $page,
        int $pageSize,
        string $searchQuery,
        string $loader,
        string $sortBy,
        string $minecraftVersion
    ): string {
        $offset = ($page - 1) * $pageSize;

        return match ($provider) {
            'modrinth' => $this->getModrinthUrl($pageSize, $searchQuery, $sortBy, $offset, $loader, $minecraftVersion),
            'curseforge' => $this->getCurseForgeUrl($pageSize, $searchQuery, $sortBy, $offset, $loader, $minecraftVersion),
        };
    }

    /**
     * Constructs Modrinth API URL.
     */
    private function getModrinthUrl(int $pageSize, string $searchQuery, string $sortBy, int $offset, string $loader, string $minecraftVersion): string
    {
        $facets = [
            ["categories:$loader"],
            ["server_side!=unsupported"],
        ];

        if ($minecraftVersion) {
            $facets[] = ["versions:$minecraftVersion"];
        }

        $facetsQuery = urlencode(json_encode($facets));

        return "search?limit={$pageSize}&query={$searchQuery}&index={$sortBy}&offset={$offset}&facets={$facetsQuery}";
    }

    /**
     * Constructs CurseForge API URL.
     */
    private function getCurseForgeUrl(int $pageSize, string $searchQuery, string $sortBy, int $offset, string $loader, string $minecraftVersion): string
    {
        $gameId = 432;
        $classId = 6;
        $sortOrder = "desc";

        return "mods/search?gameId={$gameId}&classId={$classId}&pageSize={$pageSize}&index={$offset}&searchFilter={$searchQuery}&modLoaderType={$loader}&gameVersion={$minecraftVersion}&sortField={$sortBy}&sortOrder={$sortOrder}";
    }

    /**
     * Generates pagination details from the response data.
     */
    private function getPagination(string $provider, array $data, int $page, int $pageSize): array
    {
        return match ($provider) {
            'modrinth' => [
                'total' => (int)$data['total_hits'],
                'count' => count($data['hits']),
                'per_page' => $pageSize,
                'current_page' => $page,
                'total_pages' => (int)ceil($data['total_hits'] / $pageSize),
            ],
            'curseforge' => [
                'total' => (int)$data['pagination']['totalCount'],
                'count' => (int)$data['pagination']['resultCount'],
                'per_page' => $pageSize,
                'current_page' => $page,
                'total_pages' => (int)ceil(
                    ((int)$data['pagination']['totalCount'] < 5000 
                        ? (int)$data['pagination']['totalCount'] 
                        : 5000) / $pageSize
                ),
            ],
        };
    }
    
    /**
     * Formats the response data based on the provider.
     */
    private function formatResponse(string $provider, array $data): array
    {
        return match ($provider) {
            'modrinth' => $this->formatModrinthResponse($data),
            'curseforge' => $this->formatCurseForgeResponse($data),
        };
    }

    /**
     * Formats Modrinth response data.
     */
    private function formatModrinthResponse(array $data): array
    {
        return array_map(function ($mod) {
            return [
                'provider' => 'modrinth',
                'id' => $mod['project_id'],
                'name' => $mod['title'],
                'description' => $mod['description'],
                'icon' => $mod['icon_url'],
                'downloads' => $mod['downloads'],
                'url' => "https://modrinth.com/mod/{$mod['project_id']}",
            ];
        }, $data['hits']);
    }

    /**
     * Formats CurseForge response data.
     */
    private function formatCurseForgeResponse(array $data): array
    {
        return array_map(function ($mod) {
            return [
                'provider' => 'curseforge',
                'id' => $mod['id'],
                'name' => $mod['name'],
                'description' => $mod['summary'],
                'icon' => $mod['logo']['url'] ?? null,
                'downloads' => $mod['downloadCount'] ?? 0,
                'url' => "https://www.curseforge.com/minecraft/mc-mods/{$mod['slug']}",
            ];
        }, $data['data']);
    }
}
