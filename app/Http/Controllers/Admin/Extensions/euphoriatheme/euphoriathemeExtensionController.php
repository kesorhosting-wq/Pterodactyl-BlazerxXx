<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\euphoriatheme;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;

class euphoriathemeExtensionController extends Controller

{
    public function __construct(
        private ViewFactory $view,
        private BlueprintExtensionLibrary $blueprint,
        private ConfigRepository $config,
        private SettingsRepositoryInterface $settings,
    ){}

    //BACKGROUNDS HANDLERS ---------------------------------------------------------------------------

    public function showBackgroundForm(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }
    
        $eggs = Egg::all();
        $servers = Server::all();
        $configuredEggs = $this->fetchConfiguredEggBackgrounds($request);
        $configuredServers = $this->fetchConfiguredServerBackgrounds($request);
    
        return view('blueprint.extensions.euphoriatheme.wrapper.admin.serverBackgrounds', [
            'eggs' => $eggs,
            'servers' => $servers,
            'configuredEggs' => $configuredEggs,
            'configuredServers' => $configuredServers,
        ]);
    }

    public function bulkSaveBackgrounds(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }

        $request->validate([
            'backgrounds' => 'required|array',
            'backgrounds.*.server_id' => 'nullable|exists:servers,uuid',
            'backgrounds.*.egg_id' => 'nullable|exists:eggs,id',
            'backgrounds.*.image_url' => 'required|url',
            'backgrounds.*.opacity' => 'nullable|numeric|min:0|max:1', // Validate opacity value
        ]);

        foreach ($request->input('backgrounds') as $background) {
            $serverUuid = $background['server_id'] ?? null;
            $eggId = $background['egg_id'] ?? null;
            $imageUrl = $background['image_url'];
            $opacity = $background['opacity'] ?? 1;

            if ($serverUuid) {
                // Save the background image URL and opacity for the server UUID
                $server = Server::where('uuid', $serverUuid)->first();
                if ($server) {
                    $this->blueprint->dbSet("euphoriatheme", "server_background_{$serverUuid}_image_url", $imageUrl);
                    $this->blueprint->dbSet("euphoriatheme", "server_background_{$serverUuid}_opacity", $opacity);
                }
            } elseif ($eggId) {
                // Save the background image URL and opacity for the egg ID
                $this->blueprint->dbSet("euphoriatheme", "egg_background_{$eggId}_image_url", $imageUrl);
                $this->blueprint->dbSet("euphoriatheme", "egg_background_{$eggId}_opacity", $opacity);
            }
        }

        return redirect()->back()->with('success', 'Background images saved successfully.');
    }

    public function updateAndDeleteBackgroundSettings(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }

        $request->validate([
            'backgrounds' => 'array',
            'backgrounds.*.server_id' => 'nullable|exists:servers,uuid',
            'backgrounds.*.egg_id' => 'nullable|exists:eggs,id',
            'backgrounds.*.image_url' => 'nullable|url',
            'backgrounds.*.opacity' => 'nullable|numeric|min:0|max:1', // Validate opacity value
            'delete_backgrounds' => 'array',
            'delete_backgrounds.*' => 'string', // Validate as string since it can be either UUID or egg ID
        ]);

        foreach ($request->input('backgrounds', []) as $background) {
            $serverUuid = $background['server_id'] ?? null;
            $eggId = $background['egg_id'] ?? null;
            $imageUrl = $background['image_url'] ?? null;
            $opacity = $background['opacity'] ?? 1;

            if ($serverUuid) {
                // Update the background image URL and opacity for the server UUID
                if ($imageUrl) {
                    $this->blueprint->dbSet("euphoriatheme", "server_background_{$serverUuid}_image_url", $imageUrl);
                }
                $this->blueprint->dbSet("euphoriatheme", "server_background_{$serverUuid}_opacity", $opacity);
            } elseif ($eggId) {
                // Update the background image URL and opacity for the egg ID
                if ($imageUrl) {
                    $this->blueprint->dbSet("euphoriatheme", "egg_background_{$eggId}_image_url", $imageUrl);
                }
                $this->blueprint->dbSet("euphoriatheme", "egg_background_{$eggId}_opacity", $opacity);
            }
        }

        // Handle deletion of backgrounds
        if ($request->has('delete_backgrounds')) {
            foreach ($request->input('delete_backgrounds') as $id) {
                // Check if the ID is a server UUID or an egg ID
                if (Server::where('uuid', $id)->exists()) {
                    // Clear server background
                    $this->blueprint->dbSet("euphoriatheme", "server_background_{$id}_image_url", '');
                    $this->blueprint->dbSet("euphoriatheme", "server_background_{$id}_opacity", '');
                } elseif (Egg::where('id', $id)->exists()) {
                    // Clear egg background
                    $this->blueprint->dbSet("euphoriatheme", "egg_background_{$id}_image_url", '');
                    $this->blueprint->dbSet("euphoriatheme", "egg_background_{$id}_opacity", '');
                }
            }
        }

        return redirect()->back()->with('success', 'Background settings updated successfully.');
    }
    
    public function getHWID()
    {
        // Attempt to get the MAC address
        $macAddress = trim(shell_exec("getmac")); // Windows
        if (!$macAddress) {
            $macAddress = trim(shell_exec("cat /sys/class/net/eth0/address")); // Linux
        }
    
        // Attempt to get the CPU information
        $cpuInfo = trim(shell_exec("wmic cpu get ProcessorId")); // Windows
        if (!$cpuInfo) {
            $cpuInfo = trim(shell_exec("cat /proc/cpuinfo | grep Serial | awk '{print $3}'")); // Linux
        }
    
        // Attempt to get the motherboard serial number
        $motherboardSerial = trim(shell_exec("wmic baseboard get SerialNumber")); // Windows
        if (!$motherboardSerial) {
            $motherboardSerial = trim(shell_exec("dmidecode -s baseboard-serial-number")); // Linux (requires root)
        }
    
        // Attempt to get the disk drive serial number
        $diskSerial = trim(shell_exec("wmic diskdrive get SerialNumber")); // Windows
        if (!$diskSerial) {
            $diskSerial = trim(shell_exec("lsblk -o SERIAL | tail -n 1")); // Linux
        }
    
        // Combine all available information to generate a unique HWID
        $hwidSource = $macAddress . $cpuInfo . $motherboardSerial . $diskSerial . php_uname('n');
        $hwid = hash('sha256', $hwidSource);
    
        return $hwid;
    }

    public function fetchConfiguredServerBackgrounds(Request $request = null)
    {
    $servers = Server::all();
    $configuredServers = [];

    foreach ($servers as $server) {
        $imageUrl = $this->blueprint->dbGet("euphoriatheme", "server_background_{$server->uuid}_image_url", '');
        $opacity = $this->blueprint->dbGet("euphoriatheme", "server_background_{$server->uuid}_opacity", 1); // Default opacity to 1

        if ($imageUrl) {
            $configuredServers[] = (object) [
                'uuid' => $server->uuid,
                'name' => $server->name,
                'image_url' => $imageUrl,
                'opacity' => $opacity,
            ];
        }
    }

    if ($request && $request->expectsJson()) {
        return response()->json($configuredServers);
    }

    return $configuredServers;
    }
    
    public function fetchConfiguredEggBackgrounds(Request $request = null)
    {
        $eggs = Egg::all();
        $configuredEggs = [];
    
        foreach ($eggs as $egg) {
            $imageUrl = $this->blueprint->dbGet("euphoriatheme", "egg_background_{$egg->id}_image_url", '');
            $opacity = $this->blueprint->dbGet("euphoriatheme", "egg_background_{$egg->id}_opacity", 1); // Default opacity to 1
    
            if ($imageUrl) {
                $configuredEggs[] = (object) [
                    'id' => $egg->id,
                    'name' => $egg->name,
                    'image_url' => $imageUrl,
                    'opacity' => $opacity,
                ];
            }
        }
    
        if ($request && $request->expectsJson()) {
            return response()->json($configuredEggs);
        }
    
        return $configuredEggs;
    }

    //License Handler
    public function showLicenseForm(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }
    
        $hwid = $this->blueprint->dbGet("euphoriatheme", 'hwid');
        $productId = $this->blueprint->dbGet("euphoriatheme", 'productId');
        $licenseKey = $this->blueprint->dbGet("euphoriatheme", 'licenseKey');

        if (empty($hwid)) {
            $hwid = $this->getHWID();
            $this->blueprint->dbSet("euphoriatheme", 'hwid', $hwid);
        }

        if (empty($productId)) {
            $productId = "1";
            $this->blueprint->dbSet("euphoriatheme", 'productId', $productId);
        }
    
        return view('blueprint.extensions.euphoriatheme.wrapper.admin.licensing', [
            'hwid' => $hwid,
            'productId' => $productId,
            'licenseKey' => $licenseKey,
        ]);
    }

    public function saveLicense(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }
    
        $settings = [
            'licenseKey' => $request->input('licenseKey', ''),
            'hwid' => $request->input('hwid', $this->getHWID()),
            'productId' => $request->input('productId', $this->blueprint->dbGet("euphoriatheme", '1'))
        ];
    
        // Save each setting explicitly in the database
        foreach ($settings as $key => $value) {
            $this->blueprint->dbSet("euphoriatheme", $key, $value);
        }
    
        // Redirect to /admin with a success message
        return redirect('/admin')->with('success', 'Settings updated successfully.');
    }

    //PROFILE PICTURES HANDLER ---------------------------------------------------------------------------

    public function uploadProfilePictureUrl(Request $request)
    {
        if (!$request->user()) {
            throw new AccessDeniedHttpException();
        }
        // Validate the request data
        $request->validate([
            'profile_picture_url' => 'required|url',
        ]);
    
        // Get the authenticated user
        $user = Auth::user();
        
        // Update the user's profile picture URL
        $user->profile_picture_url = $request->input('profile_picture_url');
        $user->save();
    
        return response()->json(['success' => true, 'message' => 'Profile picture updated successfully.']);
    }    

    public function resetProfilePictureUrl(Request $request)
    {
    if (!$request->user()) {
        throw new AccessDeniedHttpException();
    }
    // Get the authenticated user
    $user = Auth::user();
    
    // Clear the profile picture URL
    $user->profile_picture_url = null;
    $user->save();

    return response()->json(['success' => true, 'message' => 'Profile picture reset to default.']);
    }   

    public function uploadProfilePictureFile(Request $request)
    {
        if (!$request->user()) {
            throw new AccessDeniedHttpException();
        }

        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|file|image|mimes:svg,ico,png,jpeg,jpg|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file. Please upload a valid image file (SVG, ICO, PNG, JPEG) under 2MB.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the authenticated user
            $user = Auth::user();
            $uploadedFile = $request->file('profile_picture');
            
            // Generate filename with user UUID and original extension
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = $user->uuid . '.' . $extension;
            
            // Define upload directory
            $uploadPath = 'assets/extensions/euphoriatheme/useruploads/';
            $fullUploadPath = public_path($uploadPath);
            
            // Create directory if it doesn't exist
            if (!File::exists($fullUploadPath)) {
                File::makeDirectory($fullUploadPath, 0755, true);
            }

            // Remove any existing profile pictures for this user
            $existingFiles = File::glob($fullUploadPath . $user->uuid . '.*');
            foreach ($existingFiles as $existingFile) {
                if (File::exists($existingFile)) {
                    File::delete($existingFile);
                }
            }
            
            // Move the uploaded file to the destination
            $uploadedFile->move($fullUploadPath, $filename);
            
            // Update the user's profile picture URL to the new file
            $profilePictureUrl = '/' . $uploadPath . $filename;
            $user->profile_picture_url = $profilePictureUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully.',
                'profile_picture_url' => $profilePictureUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchProfilePicture(Request $request)
    {
        if (!$request->user()) {
            throw new AccessDeniedHttpException();
        }
    
        $user = Auth::user();
        $profilePictureUrl = $user->profile_picture_url;
        $email = $user->email;
        $language = $user->language;
    
        $profileData = [
            'email' => $email,
            'profile_picture_url' => $profilePictureUrl,
            'language' => $language,
        ];
    
        return response()->json($profileData);
    }

    /**
     * Fetch the server order for the authenticated user.
     */
    public function fetchServerOrder(Request $request)
    {
        if (!$request->user()) {
            throw new AccessDeniedHttpException();
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'order' => $user->server_order ? json_decode($user->server_order, true) : [],
        ]);
    }

    /**
     * Save the server order for the authenticated user.
     */
    public function saveServerOrder(Request $request)
    {
        if (!$request->user()) {
            throw new AccessDeniedHttpException();
        }

        $request->validate([
            'order' => 'required|array',
        ]);

        $user = Auth::user();
        $user->server_order = json_encode($request->input('order'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Server order saved successfully.',
        ]);
    }

    /**
     * Endpoint for other addons to check if Euphoria Theme is active
     * Returns status, activation state, license information, and server details
     */
    public function getEuphoriaStatus(Request $request)
    {
        // Get license information from database
        $licenseKey = $this->blueprint->dbGet("euphoriatheme", 'licenseKey');
        $hwid = $this->blueprint->dbGet("euphoriatheme", 'hwid');
        $productId = $this->blueprint->dbGet("euphoriatheme", 'productId');

        // Check if theme is activated (license key is not null/empty)
        $activated = !empty($licenseKey);

        // Get site domain root
        $siteDomain = $request->getHost();
        $siteUrl = $request->getSchemeAndHttpHost();

        // Calculate uptime (time since server started)
        $uptime = null;
        $uptimeSeconds = null;
        
        if (function_exists('sys_getloadavg')) {
            // Unix/Linux systems
            $uptimeString = @file_get_contents('/proc/uptime');
            if ($uptimeString) {
                $uptimeSeconds = (int)floatval(explode(' ', $uptimeString)[0]);
            }
        } else {
            // Windows systems - get PHP process uptime as fallback
            $uptimeSeconds = time() - $_SERVER['REQUEST_TIME'];
        }
        
        // Format uptime into human-readable format
        if ($uptimeSeconds !== null) {
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            $seconds = $uptimeSeconds % 60;
            
            $uptimeParts = [];
            if ($days > 0) $uptimeParts[] = $days . ' day' . ($days > 1 ? 's' : '');
            if ($hours > 0) $uptimeParts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) $uptimeParts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            if ($seconds > 0 && count($uptimeParts) < 2) $uptimeParts[] = $seconds . ' second' . ($seconds > 1 ? 's' : '');
            
            $uptime = [
                'formatted' => !empty($uptimeParts) ? implode(', ', $uptimeParts) : '0 seconds',
                'seconds' => $uptimeSeconds
            ];
        }

        // Get server response time (simple ping equivalent)
        $pingTime = null;
        $startTime = microtime(true);
        // Simple internal ping by checking database connectivity
        try {
            $this->blueprint->dbGet("euphoriatheme", 'licenseKey');
            $pingTime = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds
        } catch (\Exception $e) {
            $pingTime = null;
        }

        // Get primary color from settings
        $primaryColor = $this->blueprint->dbGet("euphoriatheme", 'primary_color', '#667eea');

        // Prepare data for the view
        $statusData = [
            'status' => true,
            'activated' => $activated,
            'hwid' => $hwid ?: null,
            'productId' => $productId ?: null,
            'licenseKey' => $licenseKey ? substr($licenseKey, 0, 8) . '...' . substr($licenseKey, -4) : null,
            'siteDomain' => $siteDomain,
            'siteUrl' => $siteUrl,
            'uptime' => $uptime,
            'ping' => $pingTime,
            'serverTime' => now()->format('Y-m-d H:i:s T'),
            'phpVersion' => phpversion(),
            'laravelVersion' => app()->version(),
            'primaryColor' => $primaryColor,
        ];

        return view('blueprint.extensions.euphoriatheme.status', compact('statusData'));
    }

    //Languages

    public function showLanguageForm(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }
    
        $enabledLanguages = $this->blueprint->dbGet("euphoriatheme", 'enabledLanguages') ?? [];

        if (!is_array($enabledLanguages)) {
            $enabledLanguages = json_decode($enabledLanguages, true);
        }

        // Pass configured language API URL so the admin view can use it when present
        $language_api_url = $this->blueprint->dbGet('euphoriatheme', 'language_api_url');

        return view('blueprint.extensions.euphoriatheme.wrapper.admin.languages', [
            'enabledLanguages' => $enabledLanguages,
            'language_api_url' => $language_api_url,
        ]);
    }

    public function getLanguageSettings(Request $request)
    {
        $enabledLanguages = $this->blueprint->dbGet("euphoriatheme", 'enabledLanguages') ?? [];
        $defaultLanguage = $this->blueprint->dbGet('euphoriatheme', 'default_language', 'en'); // Default to English

        if (!is_array($enabledLanguages)) {
            $enabledLanguages = json_decode($enabledLanguages, true);
        }

        return response()->json([
            'success' => true,
            'enabledLanguages' => $enabledLanguages,
            'defaultLanguage' => $defaultLanguage,
        ]);
    }

    public function saveLanguageSettings(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }

        $languages = $request->input('languages', []);
        $defaultLanguage = $request->input('defaultLanguage', 'en'); // Default to English if not provided

        $this->blueprint->dbSet("euphoriatheme", 'enabledLanguages', json_encode($languages));
        $this->blueprint->dbSet('euphoriatheme', 'default_language', $defaultLanguage);

        return response()->json(['success' => true, 'message' => 'Language settings updated successfully.']);
    }


    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->root_admin) {
            throw new AccessDeniedHttpException();
        }

        // Fetch stored theme settings
        $primary_color = $this->blueprint->dbGet("euphoriatheme", 'primary_color');
        $logo_url = $this->blueprint->dbGet("euphoriatheme", 'logo_url');
        $favicon_url = $this->blueprint->dbGet("euphoriatheme", 'favicon_url');
        $footer_text = $this->blueprint->dbGet("euphoriatheme", 'footer_text');
        $background_url = $this->blueprint->dbGet("euphoriatheme", 'background_url');
        $login_background_url = $this->blueprint->dbGet("euphoriatheme", 'login_background_url');
        $discord_url = $this->blueprint->dbGet("euphoriatheme", 'discord_url');
        $store_url = $this->blueprint->dbGet("euphoriatheme", 'store_url');
        $status_url = $this->blueprint->dbGet("euphoriatheme", 'status_url');
        $profile_picture_url_enabled = $this->blueprint->dbGet("euphoriatheme", 'profile_picture_url_enabled'); // default to true
        $background_image_enabled = $this->blueprint->dbGet("euphoriatheme", 'background_image_enabled');   
        $loading_screen_enabled = $this->blueprint->dbGet("euphoriatheme", 'loading_screen_enabled');
        $loading_screen_background_url = $this->blueprint->dbGet("euphoriatheme", 'loading_screen_background_url');
        $loading_screen_logo_url = $this->blueprint->dbGet("euphoriatheme", 'loading_screen_logo_url');
        $loading_screen_mode = $this->blueprint->dbGet("euphoriatheme", 'loading_screen_mode');
        $loading_screen_duration = $this->blueprint->dbGet("euphoriatheme", 'loading_screen_duration');
        $cookie_alert_enabled = $this->blueprint->dbGet("euphoriatheme", 'cookie_alert_enabled');
        $announcement_type = $this->blueprint->dbGet("euphoriatheme", 'announcement_type');
        $announcement_content = $this->blueprint->dbGet("euphoriatheme", 'announcement_content');
        $announcement_icon = $this->blueprint->dbGet("euphoriatheme", 'announcement_icon');
        $site_name_enabled = $this->blueprint->dbGet("euphoriatheme", 'site_name_enabled');
        $show_logo_login_enabled = $this->blueprint->dbGet("euphoriatheme", 'show_logo_login_enabled');
        $snowing_enabled = $this->blueprint->dbGet("euphoriatheme", 'snowing_enabled');
        $halloween_enabled = $this->blueprint->dbGet("euphoriatheme", 'halloween_enabled');
        $easter_enabled = $this->blueprint->dbGet("euphoriatheme", 'easter_enabled');
        $side_nav_enabled = $this->blueprint->dbGet("euphoriatheme", 'side_nav_enabled');
        $side_nav_company_enabled = $this->blueprint->dbGet("euphoriatheme", 'side_nav_company_enabled');
        $server_tooltips_enabled = $this->blueprint->dbGet("euphoriatheme", 'server_tooltips_enabled');
        $maintenance_enabled = $this->blueprint->dbGet("euphoriatheme", 'maintenance_enabled');
        $maintenance_message = $this->blueprint->dbGet("euphoriatheme", 'maintenance_message');
        $maintenance_background_url = $this->blueprint->dbGet("euphoriatheme", 'maintenance_background_url');
        $advert_enabled = $this->blueprint->dbGet("euphoriatheme", 'advert_enabled');
        $default_language = $this->blueprint->dbGet('euphoriatheme', 'default_language', 'en');
        $tx_admin_enabled = $this->blueprint->dbGet("euphoriatheme", 'tx_admin_enabled');
        $tx_admin_egg_id = $this->blueprint->dbGet("euphoriatheme", 'tx_admin_egg_id');
        $language_api_url = $this->blueprint->dbGet("euphoriatheme", 'language_api_url');
        $custom_buttons = json_decode($this->blueprint->dbGet('euphoriatheme', 'custom_buttons', '[]'), true) ?? [];
        $custom_buttons_in_side_nav = $this->blueprint->dbGet('euphoriatheme', 'custom_buttons_in_side_nav', true);
        $licenseKey = $this->blueprint->dbGet("euphoriatheme", 'licenseKey');
        $hwid = $this->blueprint->dbGet("euphoriatheme", 'hwid');
        $productId = $this->blueprint->dbGet("euphoriatheme", 'productId');

        $this->initializeDefaultSettings();

        // Pass settings to the view
        return view('blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser', 
        compact(
            'primary_color', 'logo_url', 'favicon_url', 'footer_text', 
            'background_url', 'login_background_url', 'discord_url', 'store_url', 'status_url',
            'profile_picture_url_enabled', 'background_image_enabled',
            'loading_screen_enabled', 'loading_screen_background_url', 'loading_screen_logo_url',
            'loading_screen_mode', 'loading_screen_duration',
            'cookie_alert_enabled', 'announcement_type', 'announcement_content',
            'announcement_icon', 'site_name_enabled', 'show_logo_login_enabled',
            'snowing_enabled','halloween_enabled','easter_enabled','side_nav_enabled',
            'side_nav_company_enabled', 'server_tooltips_enabled','advert_enabled', 'licenseKey', 'default_language', 
            'maintenance_enabled','maintenance_message','maintenance_background_url',
            'tx_admin_enabled', 'tx_admin_egg_id', 'language_api_url', 'hwid', 'productId',
            'custom_buttons','custom_buttons_in_side_nav'
        ));
    }

     public function update(euphoriathemeSettingsFormRequest $request): RedirectResponse
    {
        // Initialize defaults to handle checkboxes and other values explicitly
        $settings = [
            'primary_color' => $request->input('primary_color', '#ee4266'),
            'logo_url' => $request->input('logo_url', '/extensions/euphoriatheme/images/logo.png'),
            'favicon_url' => $request->input('favicon_url', '/extensions/euphoriatheme/images/logo.png'),
            'footer_text' => $request->input('footer_text', ''),
            'discord_url' => $request->input('discord_url', ''),
            'store_url' => $request->input('store_url', ''),
            'status_url' => $request->input('status_url', ''),
            'background_url' => $request->input('background_url', ''),
            'login_background_url' => $request->input('login_background_url', ''),
            'profile_picture_url_enabled' => $request->boolean('profile_picture_url_enabled', false),
            'background_image_enabled' => $request->boolean('background_image_enabled', false),
            'loading_screen_enabled' => $request->boolean('loading_screen_enabled', false),
            'loading_screen_background_url' => $request->input('loading_screen_background_url', ''),
            'loading_screen_logo_url' => $request->input('loading_screen_logo_url', ''),
            'loading_screen_mode' => $request->input('loading_screen_mode', 'timer'),
            'loading_screen_duration' => $request->input('loading_screen_duration', 4),
            'cookie_alert_enabled' => $request->boolean('cookie_alert_enabled', false),
            'announcement_type' => $request->input('announcement_type', ''),
            'announcement_content' => $request->input('announcement_content', ''),
            'announcement_icon' => $request->input('announcement_icon', ''),
            'site_name_enabled' => $request->boolean('site_name_enabled', false),
            'show_logo_login_enabled' => $request->boolean('show_logo_login_enabled', false),
            'snowing_enabled' => $request->boolean('snowing_enabled', false),
            'halloween_enabled' => $request->boolean('halloween_enabled', false),
            'easter_enabled' => $request->boolean('easter_enabled', false),
            'side_nav_enabled' => $request->boolean('side_nav_enabled', false),
            'side_nav_company_enabled' => $request->boolean('side_nav_company_enabled', false),
            'server_tooltips_enabled' => $request->boolean('server_tooltips_enabled', false),
            'maintenance_enabled' => $request->boolean('maintenance_enabled', false),
            'maintenance_message' => $request->input('maintenance_message', ''),
            'maintenance_background_url' => $request->input('maintenance_background_url', ''),
            'custom_buttons' => $request->input('custom_buttons', '[]'),
            'custom_buttons_in_side_nav' => $request->boolean('custom_buttons_in_side_nav', true),
            'default_language' => $request->input('default_language', 'en'), // Default to English if not provided
            'advert_enabled' => $request->input('advert_enabled', true),
            'tx_admin_enabled' => $request->boolean('tx_admin_enabled', false),
            'tx_admin_egg_id' => $request->input('tx_admin_egg_id', ''), 
            'language_api_url' => $request->input('language_api_url', ''),
            'hwid' => $request->input('hwid', $this->getHWID()),
            'productId' => $request->input('productId', $this->blueprint->dbGet("euphoriatheme", '1'))
        ];

        // Save each setting explicitly in the database
        foreach ($settings as $key => $value) {
            $this->blueprint->dbSet("euphoriatheme", $key, $value);
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    private function initializeDefaultSettings()
    {
    $settings = [
        'primary_color' => '#ee4266',
        'logo_url' => '/extensions/euphoriatheme/images/logo.png',
        'favicon_url' => '/extensions/euphoriatheme/images/logo.png',
        'footer_text' => '',
        'background_url' => '',
        'login_background_url' => '',
        'discord_url' => '',
        'store_url' => '',
        'status_url' => '',
        'profile_picture_url_enabled' => false,
        'background_image_enabled' => false,
        'loading_screen_enabled' => false,
        'loading_screen_background_url' => '',
        'loading_screen_logo_url' => '',
        'loading_screen_mode' => 'timer',
        'loading_screen_duration' => 4,
        'cookie_alert_enabled' => false,
        'announcement_type' => 'standard',
        'announcement_content' => 'Welcome to Euphoria Theme! Head to the Theme Customiser to get Started!',
        'announcement_icon' => 'fa-solid fa-door-open',
        'site_name_enabled' => false,
        'show_logo_login_enabled' => false,
        'snowing_enabled' => false,
        'halloween_enabled' => false,
        'easter_enabled' => false,
        'side_nav_enabled' => true,
        'side_nav_company_enabled' => false,
        'server_tooltips_enabled' => false,
        'maintenance_enabled' => false,
        'maintenance_message' => '',
        'maintenance_background_url' => '',
        'custom_buttons' => json_encode([]),
        'custom_buttons_in_side_nav' => true,
        'default_language' => 'en',
        'advert_enabled' => true,
        'tx_admin_enabled' => false,
        'tx_admin_egg_id' => '',
        'language_api_url' => '',
        'licenseKey' => '',
        'hwid' => $this->getHWID(), 
        'productId' => '1',
    ];

    foreach ($settings as $key => $default) {
        $current = $this->blueprint->dbGet("euphoriatheme", $key);
        if ($current === null || $current === '') {
            $this->blueprint->dbSet("euphoriatheme", $key, $default);
        }
        }
    }
}


class euphoriathemeSettingsFormRequest extends AdminFormRequest
{
  public function rules(): array
  {
    return [
        'primary_color' => 'required|string|max:7',
        'logo_url' => 'nullable|string',
        'favicon_url' => 'nullable|string',
        'footer_text' => 'nullable|string|max:255',
        'discord_url' => 'nullable|url',
        'store_url' => 'nullable|url',
        'status_url' => 'nullable|url',
        'background_url' => 'nullable|string',
        'login_background_url' => 'nullable|string',
        'background_image_enabled' => 'nullable|boolean',
        'profile_picture_url_enabled' => 'nullable|boolean',
        'loading_screen_enabled' => 'nullable|boolean',
        'loading_screen_background_url' => 'nullable|string',
        'loading_screen_logo_url' => 'nullable|string',
        'loading_screen_mode' => 'nullable|string|in:timer,page_load',
        'loading_screen_duration' => 'nullable|integer|min:1|max:30',
        'cookie_alert_enabled' => 'nullable|boolean',
        'announcement_type' => 'string',
        'announcement_content' => 'nullable|string|max:500',
        'announcement_icon' => 'nullable|string|max:255',
        'site_name_enabled' => 'nullable|boolean',
        'show_logo_login_enabled' => 'nullable|boolean',
        'snowing_enabled' => 'nullable|boolean',
        'halloween_enabled' => 'nullable|boolean',
        'easter_enabled' => 'nullable|boolean',
        'side_nav_enabled' => 'nullable|boolean',
        'side_nav_company_enabled' => 'nullable|boolean',
        'server_tooltips_enabled' => 'nullable|boolean',
        'maintenance_enabled' => 'nullable|boolean',
        'maintenance_message' => 'nullable|string|max:500',
        'maintenance_background_url' => 'nullable|string',
        'default_language' => 'nullable|string',
        'advert_enabled' => 'nullable|boolean',
        'tx_admin_enabled' => 'nullable|boolean',
        'tx_admin_egg_id' => 'nullable|string|max:10',
        'language_api_url' => 'nullable|url',
        'licenseKey' => 'nullable|string',
        'hwid' => 'nullable|string',
        'productId' => 'nullable|string'
    ];
  }

  public function attributes(): array
  {
    return [
        'primary_color' => 'Users Primary Color',
        'logo_url' => 'Users Logo',
        'favicon_url' => 'Users Logo',
        'footer_text' => 'Users Footer Text/HTML',
        'discord_url' => 'Users Support Discord URL',
        'store_url' => 'Users Store URL',
        'status_url' => 'Users Status Page URL',
        'background_url' => 'Users Background Image URL',
        'login_background_url' => 'Users Login Background Image URL',
        'background_image_enabled' => 'Server Background Images Toggle',
        'profile_picture_url_enabled' => 'Profile Picture Images Toggle',
        'loading_screen_enabled' => 'Loading Screens Toggle',
        'loading_screen_background_url' => 'Loading Screen Background Image URL',
        'loading_screen_logo_url' => 'Loading Screen Logo Image URL',
        'loading_screen_mode' => 'Loading Screen Mode',
        'loading_screen_duration' => 'Loading Screen Duration',
        'cookie_alert_enabled' => 'Cookies Banner Toggle',
        'announcement_type' => 'Type of Announcement',
        'announcement_content' => 'Text within Announcement',
        'announcement_icon' => 'Icon for Announcement',
        'site_name_enabled' => 'Show or Hide site name',
        'show_logo_login_enabled' => 'Show or Hide Logo on Login Page',
        'snowing_enabled' => 'Toggle Snow Effect',
        'halloween_enabled' => 'Toggle Spooky Effect',
        'easter_enabled' => 'Toggle Easter Effect',
        'side_nav_enabled' => 'Toggle Side Navigation',
        'side_nav_company_enabled' => 'Toggle Company Name when using Side Navigation',
        'server_tooltips_enabled' => 'Toggle Server Tooltips',
        'maintenance_enabled' => 'Toggle Maintenance Mode',
        'maintenance_message' => 'Maintenance Page Message',
        'maintenance_background_url' => 'Maintenance Page Background URL',
        'advert_enabled' => 'Toggle Advert',
        'default_language' => 'Default Language',
        'tx_admin_enabled' => 'TX Admin Integration Toggle',
        'tx_admin_egg_id' => 'TX Admin Egg ID',
        'language_api_url' => 'Language API URL',
        'licenseKey' => 'License Key',
        'hwid' => 'Hardware ID',
        'productId' => 'Product ID'
    ];
  }
  
}