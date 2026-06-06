<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmods;

use Illuminate\Support\Facades\Cache;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class MCModsSettingsController extends ClientApiController
{
    protected BlueprintAdminLibrary $blueprint;

    public function __construct(BlueprintAdminLibrary $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    /**
     * List all settings for MC Mods.
     */
    public function index()
    {
        $settings = Cache::rememberForever('mcmods_settings', function () {
            $keys = [
                'default_page_size',
                'default_provider',
                'text_install_button',
                'text_versions_button',
                'text_search',
                'text_search_box',
                'text_version',
                'text_loader',
                'text_sort_by',
                'text_provider',
                'text_page_size',
                'text_not_found',
                'text_showing',
                'text_version_list',
                'text_versions_not_found',
                'text_version_downloads',
                'text_redirect_url',
                'text_install_success',
                'text_install_failed',
            ];

            $settings = [];
            foreach ($keys as $key) {
                $settings[$key] = $this->blueprint->dbGet('mcmods', $key, null);
            }

            return $settings;
        });

        return response()->json($settings);
    }
}
