<?php
    $logo_url = $blueprint->dbGet("euphoriatheme", 'logo_url');
    $favicon_url = $blueprint->dbGet("euphoriatheme", 'favicon_url');
    $footer_text = $blueprint->dbGet("euphoriatheme", 'footer_text');
    $discord_url = $blueprint->dbGet("euphoriatheme", 'discord_url');
    $store_url = $blueprint->dbGet("euphoriatheme", 'store_url');
    $status_url = $blueprint->dbGet("euphoriatheme", 'status_url');
    $background_url = $blueprint->dbGet("euphoriatheme", 'background_url');
    $primary_color = $blueprint->dbGet("euphoriatheme", 'primary_color', '#C2185B');
    $login_background_url = $blueprint->dbGet("euphoriatheme", 'login_background_url');
    $announcement_type = $blueprint->dbGet("euphoriatheme", 'announcement_type');  
    $announcement_content = $blueprint->dbGet("euphoriatheme", 'announcement_content');  
    $announcement_icon = $blueprint->dbGet("euphoriatheme", 'announcement_icon');     
    $profile_picture_url_enabled = $blueprint->dbGet('euphoriatheme', 'profile_picture_url_enabled'); 
    $loading_screen_enabled = $blueprint->dbGet("euphoriatheme", 'loading_screen_enabled');   
    $loading_screen_background_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_background_url');
    $loading_screen_logo_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_logo_url');
    $loading_screen_mode = $blueprint->dbGet("euphoriatheme", 'loading_screen_mode');
    $loading_screen_duration = $blueprint->dbGet("euphoriatheme", 'loading_screen_duration');
    $cookie_alert_enabled = $blueprint->dbGet("euphoriatheme", 'cookie_alert_enabled');
    $api_url = $blueprint->dbGet("euphoriatheme", 'api_url');
    $server_tooltips_enabled = $blueprint->dbGet("euphoriatheme", 'server_tooltips_enabled');
    $show_logo_login_enabled = $blueprint->dbGet("euphoriatheme", 'show_logo_login_enabled');
    $side_nav_company_enabled = $blueprint->dbGet("euphoriatheme", 'side_nav_company_enabled');
    $side_nav_enabled = $blueprint->dbGet("euphoriatheme", 'side_nav_enabled');
    $advert_enabled = $blueprint->dbGet("euphoriatheme", 'advert_enabled');
    $tx_admin_enabled = $blueprint->dbGet("euphoriatheme", 'tx_admin_enabled');
    $tx_admin_egg_id = $blueprint->dbGet("euphoriatheme", 'tx_admin_egg_id');
    $language_api_url = $blueprint->dbGet("euphoriatheme", 'language_api_url');
    $maintenance_enabled = $blueprint->dbGet("euphoriatheme", 'maintenance_enabled');
    $siteConfiguration = $siteConfiguration ?? [];
    $siteConfiguration['api_url'] = $api_url;
    $siteConfiguration['logo_url'] = $logo_url;
    $siteConfiguration['store_url'] = $store_url;
    $siteConfiguration['status_url'] = $status_url;
    $siteConfiguration['discord_url'] = $discord_url;
    $siteConfiguration['server_tooltips_enabled'] = $server_tooltips_enabled;
    $siteConfiguration['maintenance_enabled'] = $maintenance_enabled;
    $siteConfiguration['side_nav_company_enabled'] = $side_nav_company_enabled;
    $siteConfiguration['advert_enabled'] = $advert_enabled;
    $siteConfiguration['side_nav_enabled'] = $side_nav_enabled;
    $siteConfiguration['show_logo_login_enabled'] = $show_logo_login_enabled;
    $siteConfiguration['tx_admin_enabled'] = $tx_admin_enabled;
    $siteConfiguration['tx_admin_egg_id'] = $tx_admin_egg_id;
    $siteConfiguration['language_api_url'] = $language_api_url;
    $siteConfiguration['loading_screen_mode'] = $loading_screen_mode;
    $siteConfiguration['loading_screen_duration'] = $loading_screen_duration;
    $siteConfiguration['custom_buttons_in_side_nav'] = $blueprint->dbGet('euphoriatheme', 'custom_buttons_in_side_nav', true);
    $siteConfiguration['custom_buttons'] = json_decode($blueprint->dbGet('euphoriatheme', 'custom_buttons', '[]'), true) ?? [];
    $licenseKey = $blueprint->dbGet("euphoriatheme", 'licenseKey');
    $enabledLanguages = $blueprint->dbGet("euphoriatheme", 'enabledLanguages', []);
?>    

@if(Auth::user() && Auth::user()->root_admin && empty($licenseKey))
    <?php header('Location: /admin'); exit; ?>
@endif

<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Pterodactyl') }}</title>

        <style>
        :root {
            --primary-color: {{ $primary_color }};
        }
        </style>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="noindex">
        @if(isset($favicon_url) && !empty($favicon_url))
            <!-- User's custom favicon -->
            <link rel="icon" type="image/png" href="{{ $favicon_url }}" sizes="32x32">
            <link rel="apple-touch-icon" sizes="180x180" href="{{ $favicon_url }}">
            <link rel="icon" type="image/png" href="{{ $favicon_url }}" sizes="16x16">
            <link rel="shortcut icon" href="{{ $favicon_url }}">
        @else
            <!-- Default favicons -->
            <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/logo.png" sizes="32x32">
            <link rel="apple-touch-icon" sizes="180x180" href="/assets/extensions/euphoriatheme/logo.png">
            <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/logo.png" sizes="16x16">
            <link rel="shortcut icon" href="/assets/extensions/euphoriatheme/logo.png">
        @endif
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
            <meta name="theme-color" content="#0e4688">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.PterodactylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
        @show
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        </style>
    </head>

    <body>
        @section('content')
            <?php $currentPath = request()->getPathInfo(); ?>
            @if(!empty($siteConfiguration['maintenance_enabled']))
                {{-- Maintenance enabled (site-wide). Allow guests (so they can login). If signed-in: allow admins, block non-admins. --}}
                @if(!Auth::check())
                    {{-- Guest users: show normal site (login etc.) --}}
                    @if(!$side_nav_enabled)
                        @include('blueprint.extensions.euphoriatheme.wrapper.navigation.navigation')
                    @endif
                    @if($side_nav_enabled)
                        @include('blueprint.extensions.euphoriatheme.wrapper.navigation.custom-buttons')
                    @endif
                    @include('blueprint.extensions.euphoriatheme.wrapper.styles.import')
                    @include('blueprint.extensions.euphoriatheme.wrapper.styles.loader')
                    @include('blueprint.extensions.euphoriatheme.wrapper.container.language')
                    @yield('above-container')
                    @yield('container')
                    @yield('below-container')
                    @include('blueprint.extensions.euphoriatheme.wrapper.footer.copyrightFooter')
                @else
                    @if(Auth::user()->root_admin)
                        {{-- Admins see normal site --}}
                        @if(!$side_nav_enabled)
                            @include('blueprint.extensions.euphoriatheme.wrapper.navigation.navigation')
                        @endif
                        @if($side_nav_enabled)
                            @include('blueprint.extensions.euphoriatheme.wrapper.navigation.custom-buttons')
                        @endif
                        @include('blueprint.extensions.euphoriatheme.wrapper.styles.import')
                        @include('blueprint.extensions.euphoriatheme.wrapper.styles.loader')
                        @include('blueprint.extensions.euphoriatheme.wrapper.container.language')
                        @yield('above-container')
                        @yield('container')
                        @yield('below-container')
                        @include('blueprint.extensions.euphoriatheme.wrapper.footer.copyrightFooter')
                    @else
                        {{-- Signed-in non-admin: show maintenance page --}}
                        @include('blueprint.extensions.euphoriatheme.maintenance')
                    @endif
                @endif
            @else
                @if(!$side_nav_enabled)
                    @include('blueprint.extensions.euphoriatheme.wrapper.navigation.navigation')
                @endif
                @if($side_nav_enabled)
                    @include('blueprint.extensions.euphoriatheme.wrapper.navigation.custom-buttons')
                @endif
                @include('blueprint.extensions.euphoriatheme.wrapper.styles.import')
                @include('blueprint.extensions.euphoriatheme.wrapper.styles.loader')
                @include('blueprint.extensions.euphoriatheme.wrapper.container.language')
                @yield('above-container')
                @yield('container')
                @yield('below-container')
                @include('blueprint.extensions.euphoriatheme.wrapper.footer.copyrightFooter')
            @endif
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show
    </body>
</html>