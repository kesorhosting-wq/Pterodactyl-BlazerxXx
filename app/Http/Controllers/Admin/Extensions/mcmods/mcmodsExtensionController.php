<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\mcmods;

use GuzzleHttp\Client;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class mcmodsExtensionController extends Controller
{
    protected BlueprintAdminLibrary $blueprint;

    public function __construct(BlueprintAdminLibrary $blueprint, protected AlertsMessageBag $alert)
    {
        $this->blueprint = $blueprint;
    }

    public function index(): View
    {
        $config = [
            'curseforge_api_key' => $this->blueprint->dbGet('mcmods', 'curseforge_api_key', null),
            'default_page_size' => $this->blueprint->dbGet('mcmods', 'default_page_size', null),
            'default_provider' => $this->blueprint->dbGet('mcmods', 'default_provider', null),
            'text_install_button' => $this->blueprint->dbGet('mcmods', 'text_install_button', null),
            'text_versions_button' => $this->blueprint->dbGet('mcmods', 'text_versions_button', null),
            'text_search' => $this->blueprint->dbGet('mcmods', 'text_search', null),
            'text_search_box' => $this->blueprint->dbGet('mcmods', 'text_search_box', null),
            'text_version' => $this->blueprint->dbGet('mcmods', 'text_version', null),
            'text_loader' => $this->blueprint->dbGet('mcmods', 'text_loader', null),
            'text_sort_by' => $this->blueprint->dbGet('mcmods', 'text_sort_by', null),
            'text_provider' => $this->blueprint->dbGet('mcmods', 'text_provider', null),
            'text_page_size' => $this->blueprint->dbGet('mcmods', 'text_page_size', null),
            'text_not_found' => $this->blueprint->dbGet('mcmods', 'text_not_found', null),
            'text_showing' => $this->blueprint->dbGet('mcmods', 'text_showing', null),
            'text_version_list' => $this->blueprint->dbGet('mcmods', 'text_version_list', null),
            'text_versions_not_found' => $this->blueprint->dbGet('mcmods', 'text_versions_not_found', null),
            'text_version_downloads' => $this->blueprint->dbGet('mcmods', 'text_version_downloads', null),
            'text_redirect_url' => $this->blueprint->dbGet('mcmods', 'text_redirect_url', null),
            'text_install_success' => $this->blueprint->dbGet('mcmods', 'text_install_success', null),
            'text_install_failed' => $this->blueprint->dbGet('mcmods', 'text_install_failed', null),
        ];

        $latestRelease = $this->fetchLatestRelease();

        return view('admin.extensions.mcmods.index', [
            'config' => $config,
            'latestRelease' => $latestRelease,
            'blueprint' => $this->blueprint,
        ]);
    }

    public function post(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'curseforge_api_key' => 'nullable|string|max:255',
            'default_page_size' => 'nullable|integer|min:1|max:48',
            'default_provider' => 'nullable|string|max:255',
            'text_install_button' => 'nullable|string|max:255',
            'text_versions_button' => 'nullable|string|max:255',
            'text_search' => 'nullable|string|max:255',
            'text_search_box' => 'nullable|string|max:255',
            'text_version' => 'nullable|string|max:255',
            'text_loader' => 'nullable|string|max:255',
            'text_sort_by' => 'nullable|string|max:255',
            'text_provider' => 'nullable|string|max:255',
            'text_page_size' => 'nullable|string|max:255',
            'text_not_found' => 'nullable|string|max:255',
            'text_showing' => 'nullable|string|max:255',
            'text_version_list' => 'nullable|string|max:255',
            'text_versions_not_found' => 'nullable|string|max:255',
            'text_version_downloads' => 'nullable|string|max:255',
            'text_redirect_url' => 'nullable|string|max:255',
            'text_install_success' => 'nullable|string|max:255',
            'text_install_failed' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            $this->blueprint->dbSet('mcmods', $key, $value);
        }

        Cache::forget('mcmods_settings');

        $this->alert->success('Configurations updated successfully!')->flash();
        return redirect()->route('admin.extensions.mcmods.index');
    }

    /**
     * Fetches the latest version of MC Mods from GitHub Releases.
     */
    private function fetchLatestRelease(): string
    {
        try {
            return Cache::remember('mcmods_latest_version', 60, function () {
                $client = new Client();
                $response = $client->get('https://api.github.com/repos/StellarStudiosXYZ/mcmods-blueprint/releases/latest', [
                    'headers' => [
                        'Accept' => 'application/vnd.github.v3+json',
                    ],
                ]);
                $data = json_decode($response->getBody(), true);
                return $data['tag_name'];
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch MC Mods latest release data: ' . $e->getMessage());
            return "Unknown";
        }
    }
}
