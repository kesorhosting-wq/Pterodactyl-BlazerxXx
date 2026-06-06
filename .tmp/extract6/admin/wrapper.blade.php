<?php
    $logo_url = $blueprint->dbGet("euphoriatheme", 'logo_url') ?? '/extensions/euphoriatheme/images/logo.png';
    $favicon_url = $blueprint->dbGet("euphoriatheme", 'favicon_url') ?? '/extensions/euphoriatheme/images/logo.png';
    $footer_text = $blueprint->dbGet("euphoriatheme", 'footer_text');
    $discord_url = $blueprint->dbGet("euphoriatheme", 'discord_url');
    $store_url = $blueprint->dbGet("euphoriatheme", 'store_url');
    $status_url = $blueprint->dbGet("euphoriatheme", 'status_url');
    $background_url = $blueprint->dbGet("euphoriatheme", 'background_url') ?? '/extensions/euphoriatheme/images/backdrop.png';
    $primary_color = $blueprint->dbGet("euphoriatheme", 'primary_color', '#C2185B');
    $login_background_url = $blueprint->dbGet("euphoriatheme", 'login_background_url') ?? '/extensions/euphoriatheme/images/backdrop.png';
    $announcement_type = $blueprint->dbGet("euphoriatheme", 'announcement_type');
    $announcement_content = $blueprint->dbGet("euphoriatheme", 'announcement_content');
    $announcement_icon = $blueprint->dbGet("euphoriatheme", 'announcement_icon');
    $profile_picture_url_enabled = $blueprint->dbGet('euphoriatheme', 'profile_picture_url_enabled', true);
    $loading_screen_enabled = $blueprint->dbGet("euphoriatheme", 'loading_screen_enabled', false);
    $loading_screen_background_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_background_url');
    $loading_screen_logo_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_logo_url');
    $cookie_alert_enabled = $blueprint->dbGet("euphoriatheme", 'cookie_alert_enabled', false);
    $licenseKey = $blueprint->dbGet("euphoriatheme", 'licenseKey');
    $hwid = $blueprint->dbGet("euphoriatheme", 'hwid');
    $productId = $blueprint->dbGet("euphoriatheme", 'productId');
    $advert_enabled = $blueprint->dbGet("euphoriatheme", 'advert_enabled', false);
?>

<!DOCTYPE html>
    <head>
        <title>{{ config('app.name', 'Pterodactyl') }} - @yield('title')</title>

        <style>
        :root {
            --primary-color: {{ $primary_color }};
        }
        </style>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="_token" content="{{ csrf_token() }}">
            @if(isset($favicon_url) && !empty($favicon_url))
                <!-- User's custom favicon -->
                <link rel="icon" type="image/png" href="{{ $favicon_url }}" sizes="32x32">
                <link rel="icon" type="image/png" href="{{ $favicon_url }}" sizes="16x16">
                <link rel="shortcut icon" href="{{ $favicon_url }}">
            @endif
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="/extensions/euphoriatheme/lib/tippy.css?{timestamp}" />
            <link rel="stylesheet" href="/extensions/euphoriatheme/lib/floating-ui.css?{timestamp}">
            <script src="/extensions/euphoriatheme/lib/popper.min.js?{timestamp}"></script>
            <script src="/extensions/euphoriatheme/lib/tippy-bundle.umd.min.js?{timestamp}"></script>
           @show
    </head>

    @if(request()->is('admin'))
    <!-- Floating buttons -->
    <div class="floating-buttons">
        <!-- Mobile hamburger toggle button -->
        <button class="floating-menu-toggle" onclick="toggleFloatingMenu()">
            <i class="fa-solid fa-bars"></i>
        </button>
        
        <!-- Menu items -->
        <div class="floating-menu-items">
            <a href="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.serverBackgrounds') }}" class="{{ Route::currentRouteName() == 'blueprint.extensions.euphoriatheme.wrapper.admin.serverBackgrounds' ? 'active' : '' }}" data-tippy-content="Server Background">
                <i class="fa-solid fa-image"></i>
            </a>
            <a href="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.languages') }}" class="{{ Route::currentRouteName() == 'blueprint.extensions.euphoriatheme.wrapper.admin.languages' ? 'active' : '' }}" data-tippy-content="Language Settings">
                <i class="fa-solid fa-language"></i>
            </a>
            <a href="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser') }}" class="{{ Route::currentRouteName() == 'blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser' ? 'active' : '' }}" data-tippy-content="Theme Customiser">
                <i class="fa-solid fa-palette"></i>
            </a>
            <a id="license-button" href="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.licensing') }}" class="bg-gray-500 flex items-center px-4 py-2 rounded-lg text-white" data-tippy-content="License Status: Checking...">
                <i class="fa-solid fa-key mr-2"></i>
            </a>
        </div>
    </div>
    
    <script>
        function toggleFloatingMenu() {
            const toggle = document.querySelector('.floating-menu-toggle');
            const menu = document.querySelector('.floating-menu-items');
            
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        }
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const floatingButtons = document.querySelector('.floating-buttons');
            if (!floatingButtons.contains(event.target)) {
                const toggle = document.querySelector('.floating-menu-toggle');
                const menu = document.querySelector('.floating-menu-items');
                toggle.classList.remove('active');
                menu.classList.remove('active');
            }
        });
        
        // Close menu when clicking on a menu item
        document.querySelectorAll('.floating-menu-items a').forEach(item => {
            item.addEventListener('click', function() {
                const toggle = document.querySelector('.floating-menu-toggle');
                const menu = document.querySelector('.floating-menu-items');
                toggle.classList.remove('active');
                menu.classList.remove('active');
            });
        });
    </script>
    @endif

    <script>
    @if(Auth::user() && Auth::user()->root_admin && empty($licenseKey))
    document.addEventListener('DOMContentLoaded', async () => {
        const licenseButton = document.querySelector('#license-button');
        licenseButton.classList.add('bg-red-500');
        licenseButton.classList.remove('bg-green-500', 'bg-gray-500');
        
        // Update tooltip for missing license
        if (licenseButton._tippy) {
            licenseButton._tippy.setContent('License Status: No License Key');
        }
    });
    @endif
    </script>

@if(Auth::user() && Auth::user()->root_admin && !empty($licenseKey) && request()->is('admin'))
<script>
   document.addEventListener('DOMContentLoaded', async () => {
    const licenseButton = document.querySelector('#license-button');
    
    // Initialize the tooltip first
    tippy('[data-tippy-content]', {
        placement: 'left',
        theme: 'dark',
    });
    
    try {
        const sourceDomain = window.location.origin;
        const response = await fetch('https://api.euphoriadevelopment.uk/license/verify-license', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                licenseKey: '{{ $licenseKey }}',
                hwid: '{{ $hwid }}',
                productId: '{{ $productId }}',
                source: sourceDomain,
            }),
        });

        const data = await response.json();

        if (data.success === true) {
                // Set button to green and update text to "Valid"
                licenseButton.classList.add('bg-green-500');
                licenseButton.classList.remove('bg-red-500', 'bg-gray-500');
                
                // Update tooltip for valid license
                if (licenseButton._tippy) {
                    licenseButton._tippy.setContent('License Status: Valid');
                }
            } else if (data.success === false) {
                // Set button to red and update text to "Invalid"
                licenseButton.classList.add('bg-red-500');
                licenseButton.classList.remove('bg-green-500', 'bg-gray-500');
                
                // Update tooltip for invalid license
                if (licenseButton._tippy) {
                    licenseButton._tippy.setContent('License Status: Invalid');
                }
            } else {
                // Handle unexpected responses
                throw new Error('Unexpected API response.');
            }
        } catch (error) {
            console.error('Error verifying license:', error);
            // Set button to red and update text to "API Timeout" in case of an error
            licenseButton.classList.add('bg-red-500');
            licenseButton.classList.remove('bg-green-500', 'bg-gray-500');
            
            // Update tooltip for connection error
            if (licenseButton._tippy) {
                licenseButton._tippy.setContent('License Status: Connection Error');
            }
        }
});
</script>
@endif