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
    $background_image_enabled = $blueprint->dbGet("euphoriatheme", 'background_image_enabled');
    $server_hover_glow_enabled = $blueprint->dbGet("euphoriatheme", 'server_hover_glow_enabled');
    $site_name_enabled = $blueprint->dbGet("euphoriatheme", 'site_name_enabled');
    $show_logo_login_enabled = $blueprint->dbGet("euphoriatheme", 'show_logo_login_enabled');
    $server_internal_glow_disabled = $blueprint->dbGet("euphoriatheme", 'server_internal_glow_disabled');
    $side_nav_enabled = $blueprint->dbGet("euphoriatheme", 'side_nav_enabled');
    $server_tooltips_enabled = $blueprint->dbGet("euphoriatheme", 'server_tooltips_enabled');
    $cookie_alert_enabled = $blueprint->dbGet("euphoriatheme", 'cookie_alert_enabled');
    $halloween_enabled = $blueprint->dbGet("euphoriatheme", 'halloween_enabled');
    $snowing_enabled = $blueprint->dbGet("euphoriatheme", 'snowing_enabled');
    $easter_enabled = $blueprint->dbGet("euphoriatheme", 'easter_enabled');
    $advert_enabled = $blueprint->dbGet("euphoriatheme", 'advert_enabled');
?>

<head>
@if(Auth::check())
    <script src="/extensions/euphoriatheme/lib/sortable.min.js?{timestamp}"></script>    <!-- Include Conditional styles -->
    <link rel="stylesheet" href="/extensions/euphoriatheme/lib/sorting.css?{timestamp}">
    <link rel="stylesheet" href="/extensions/euphoriatheme/lib/announcement.css?{timestamp}">
    @if($cookie_alert_enabled)
        <link rel="stylesheet" href="/extensions/euphoriatheme/lib/cookies.css?{timestamp}">
    @endif
    @if($halloween_enabled)
        <link rel="stylesheet" href="/extensions/euphoriatheme/lib/halloween.css?{timestamp}">
    @endif
    @if($snowing_enabled)
        <link rel="stylesheet" href="/extensions/euphoriatheme/lib/christmas.css?{timestamp}">
    @endif
    @if($easter_enabled)
        <link rel="stylesheet" href="/extensions/euphoriatheme/lib/easter.css?{timestamp}">
    @endif

    <script src="/extensions/euphoriatheme/lib/sorting.js?{timestamp}"></script>
    <!-- Include Conditional scripts -->
    @if($cookie_alert_enabled)
        <script src="/extensions/euphoriatheme/lib/cookies.js?{timestamp}"></script>
    @endif
    @if($halloween_enabled)
        <script src="/extensions/euphoriatheme/lib/halloween.js?{timestamp}"></script>
    @endif
    @if($snowing_enabled)
        <script src="/extensions/euphoriatheme/lib/christmas.js?{timestamp}"></script>
    @endif
    @if($easter_enabled)
        <script src="/extensions/euphoriatheme/lib/easter.js?{timestamp}"></script>
    @endif
@endif

<script src="/extensions/euphoriatheme/lib/language.js?{timestamp}"></script>
<script src="/extensions/euphoriatheme/lib/translations.js?{timestamp}"></script>

@if(!Auth::check() && $advert_enabled)
    <link rel="stylesheet" href="/extensions/euphoriatheme/lib/advert.css?{timestamp}">
@endif

@if(Auth::check())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), delay);
        };
    };

    const backgroundImageEnabled = {{ $background_image_enabled ? 'true' : 'false' }};

    const checkAnnouncementVisibility = () => {
        const currentPath = window.location.pathname;
        const announcementWrapper = document.querySelector('.announcement-wrapper');
        const closeBtn = document.getElementById('announcement-close-btn');
        const announcementKey = 'announcementLastClosed';

        if (announcementWrapper) {
            const lastClosed = localStorage.getItem(announcementKey);
            const now = Date.now();
            const shouldShowAnnouncement = !lastClosed || now - lastClosed > 24 * 60 * 60 * 1000;

            announcementWrapper.style.display = shouldShowAnnouncement && currentPath === '/' ? 'flex' : 'none';

            if (shouldShowAnnouncement && currentPath === '/') {
                announcementWrapper.style.width = '100%';
                announcementWrapper.style.justifyContent = 'center';
            }
        }

        const closeAnnouncement = () => {
            if (announcementWrapper) {
                announcementWrapper.style.display = 'none';
                localStorage.setItem(announcementKey, Date.now());
            }
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', closeAnnouncement);
        }
    };

    const updateServerBackgrounds = debounce(async () => {
        if (!backgroundImageEnabled) {
            document.querySelectorAll('.background-image').forEach(el => el.remove());
            return;
        }

        const toggleSwitch = document.querySelector('input[name="show_all_servers"]');
        const apiUrl = toggleSwitch && toggleSwitch.checked
            ? '/api/client?page=1&type=admin'
            : '/api/client?page=1';
            
        try {
            const [serverData, configuredEggs, configuredServerBackgrounds] = await Promise.all([
                fetch(apiUrl).then(response => response.json()),
                fetch('/extensions/euphoriatheme/configured-egg-backgrounds').then(response => response.json()),
                fetch('/extensions/euphoriatheme/configured-server-backgrounds').then(response => response.json())
            ]);

            if (!serverData.data || !configuredEggs || !configuredServerBackgrounds) {
                console.warn("No server data, configured egg backgrounds, or configured server backgrounds found.");
                return;
            }

            const filteredServers = serverData.data.map(server => ({
                egg_id: server.attributes.BlueprintFramework.egg_id,
                name: server.attributes.name,
                uuid: server.attributes.uuid,
                identifier: server.attributes.identifier
            }));

            document.querySelectorAll('.dyLna-D').forEach((container) => {
                const hrefLink = container.getAttribute('href');
                if (!hrefLink) return;

                const serverId = hrefLink.split('/').pop();
                const server = filteredServers.find(s => s.identifier === serverId);
                if (!server) return;

                const configuredServerBackground = configuredServerBackgrounds.find(bg => bg.uuid === server.uuid);
                const configuredEgg = configuredEggs.find(egg => egg.id === server.egg_id);

                let backgroundImageUrl = '';
                let backgroundOpacity = 1;

                if (configuredServerBackground) {
                    backgroundImageUrl = configuredServerBackground.image_url;
                    backgroundOpacity = configuredServerBackground.opacity;
                } else if (configuredEgg) {
                    backgroundImageUrl = configuredEgg.image_url;
                    backgroundOpacity = configuredEgg.opacity;
                }

                if (backgroundImageUrl) {
                    // **Check if background already exists**
                    if (!container.querySelector('.background-image')) {
                        // Create an img for fade-in, then set as background when loaded
                        const tempImg = document.createElement('img');
                        tempImg.src = `${backgroundImageUrl}?cache_bust=${Date.now()}`;
                        tempImg.style.opacity = '0';
                        tempImg.style.position = 'absolute';
                        tempImg.style.top = '0';
                        tempImg.style.left = '0';
                        tempImg.style.width = '100%';
                        tempImg.style.height = '100%';
                        tempImg.style.objectFit = 'cover';
                        tempImg.style.transition = 'opacity 0.6s';
                        tempImg.onload = function() {
                            tempImg.style.opacity = backgroundOpacity;
                        };
                        tempImg.className = 'background-image';
                        container.appendChild(tempImg);
                    }
                }
            });
        } catch (error) {
            console.error('Error fetching server data or configured backgrounds:', error);
        }
    }, 500);

    const forceBackgroundRefresh = () => {
        document.querySelectorAll('.background-image').forEach(el => el.remove());
        updateServerBackgrounds();
    };

    const reattachToggleListener = () => {
        const toggleSwitch = document.querySelector('input[name="show_all_servers"]');
        if (toggleSwitch) {
            toggleSwitch.removeEventListener('change', forceBackgroundRefresh);
            toggleSwitch.addEventListener('change', forceBackgroundRefresh);
        }
    };

    const initializePage = () => {
        if (window.location.pathname === '/') {
            updateServerBackgrounds();
            reattachToggleListener();
        }
    };

    initializePage();

    window.addEventListener('load', () => {
        checkAnnouncementVisibility();
        initializePage();
    });

    window.addEventListener('popstate', () => {
        checkAnnouncementVisibility();
        forceBackgroundRefresh();
    });

    let lastPathname = window.location.pathname;
    setInterval(() => {
        if (window.location.pathname !== lastPathname) {
            lastPathname = window.location.pathname;
            checkAnnouncementVisibility();
            forceBackgroundRefresh();
        }
    }, 500);

    const observer = new MutationObserver(() => {
        updateServerBackgrounds();
    });

    const appDiv = document.querySelector("#app");
    if (appDiv) {
        observer.observe(appDiv, { childList: true, subtree: true });
    }
});
</script>
@endif
    <style>
        :root {
            --primary-color: {{ $primary_color }};
        }

        .bg-neutral-900 {
            @if(!empty($login_background_url))
                opacity: 0;
                animation: fadeInBg 0.7s ease-in forwards;
                background-image: url('{{ $login_background_url }}') !important;
                background-attachment: fixed;
                background-size: cover;
                background-position: center center;
                background-color: hsla(0, 0%, 0%, 0.97) !important; /* Fallback background color */
            @else
                opacity: 0;
                background-color: hsl(234.09deg 53.37% 3.01% / 97%); /* Fallback background color */
                background-size: cover;
                animation: fadeInBg 0.7s ease-in forwards;
                background-position: center center;
                background-repeat: repeat;
                background-attachment: fixed;
                background-image: url("data:image/svg+xml;utf8,%3Csvg viewBox=%220 0 2000 1400%22 xmlns=%22http:%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cmask id=%22b%22 x=%220%22 y=%220%22 width=%222000%22 height=%221400%22%3E%3Cpath fill=%22url(%23a)%22 d=%22M0 0h2000v1400H0z%22%2F%3E%3C%2Fmask%3E%3Cpath d=%22M0 0h2000v1400H0z%22%2F%3E%3Cg style=%22transform-origin:center center%22 stroke=%22%23979797%22 stroke-width=%22.2%22 fill=%22none%22 mask=%22url(%23b)%22%3E%3Cpath d=%22M0 0h117.647v117.647H0zM117.647 0h117.647v117.647H117.647zM235.294 0h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 0h117.647v117.647H352.941zM470.588 0h117.647v117.647H470.588zM588.235 0h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 0h117.647v117.647H705.882zM823.529 0h117.647v117.647H823.529zM941.176 0h117.647v117.647H941.176zM1058.824 0h117.647v117.647h-117.647zM1176.471 0h117.647v117.647h-117.647zM1294.118 0h117.647v117.647h-117.647zM1411.765 0h117.647v117.647h-117.647zM1529.412 0h117.647v117.647h-117.647zM1647.059 0h117.647v117.647h-117.647zM1764.706 0h117.647v117.647h-117.647zM1882.353 0H2000v117.647h-117.647zM0 117.647h117.647v117.647H0zM117.647 117.647h117.647v117.647H117.647zM235.294 117.647h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 117.647h117.647v117.647H352.941zM470.588 117.647h117.647v117.647H470.588zM588.235 117.647h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 117.647h117.647v117.647H705.882zM823.529 117.647h117.647v117.647H823.529zM941.176 117.647h117.647v117.647H941.176zM1058.824 117.647h117.647v117.647h-117.647zM1176.471 117.647h117.647v117.647h-117.647zM1294.118 117.647h117.647v117.647h-117.647zM1411.765 117.647h117.647v117.647h-117.647zM1529.412 117.647h117.647v117.647h-117.647zM1647.059 117.647h117.647v117.647h-117.647zM1764.706 117.647h117.647v117.647h-117.647zM1882.353 117.647H2000v117.647h-117.647zM0 235.294h117.647v117.647H0zM117.647 235.294h117.647v117.647H117.647zM235.294 235.294h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 235.294h117.647v117.647H352.941zM470.588 235.294h117.647v117.647H470.588zM588.235 235.294h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 235.294h117.647v117.647H705.882zM823.529 235.294h117.647v117.647H823.529zM941.176 235.294h117.647v117.647H941.176zM1058.824 235.294h117.647v117.647h-117.647zM1176.471 235.294h117.647v117.647h-117.647zM1294.118 235.294h117.647v117.647h-117.647zM1411.765 235.294h117.647v117.647h-117.647zM1529.412 235.294h117.647v117.647h-117.647zM1647.059 235.294h117.647v117.647h-117.647zM1764.706 235.294h117.647v117.647h-117.647zM1882.353 235.294H2000v117.647h-117.647zM0 352.941h117.647v117.647H0zM117.647 352.941h117.647v117.647H117.647zM235.294 352.941h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 352.941h117.647v117.647H352.941zM470.588 352.941h117.647v117.647H470.588zM588.235 352.941h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 352.941h117.647v117.647H705.882zM823.529 352.941h117.647v117.647H823.529zM941.176 352.941h117.647v117.647H941.176zM1058.824 352.941h117.647v117.647h-117.647zM1176.471 352.941h117.647v117.647h-117.647zM1294.118 352.941h117.647v117.647h-117.647zM1411.765 352.941h117.647v117.647h-117.647zM1529.412 352.941h117.647v117.647h-117.647zM1647.059 352.941h117.647v117.647h-117.647zM1764.706 352.941h117.647v117.647h-117.647zM1882.353 352.941H2000v117.647h-117.647zM0 470.588h117.647v117.647H0zM117.647 470.588h117.647v117.647H117.647zM235.294 470.588h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 470.588h117.647v117.647H352.941zM470.588 470.588h117.647v117.647H470.588zM588.235 470.588h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 470.588h117.647v117.647H705.882zM823.529 470.588h117.647v117.647H823.529zM941.176 470.588h117.647v117.647H941.176zM1058.824 470.588h117.647v117.647h-117.647zM1176.471 470.588h117.647v117.647h-117.647zM1294.118 470.588h117.647v117.647h-117.647zM1411.765 470.588h117.647v117.647h-117.647zM1529.412 470.588h117.647v117.647h-117.647zM1647.059 470.588h117.647v117.647h-117.647zM1764.706 470.588h117.647v117.647h-117.647zM1882.353 470.588H2000v117.647h-117.647zM0 588.235h117.647v117.647H0zM117.647 588.235h117.647v117.647H117.647zM235.294 588.235h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 588.235h117.647v117.647H352.941zM470.588 588.235h117.647v117.647H470.588zM588.235 588.235h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 588.235h117.647v117.647H705.882zM823.529 588.235h117.647v117.647H823.529zM941.176 588.235h117.647v117.647H941.176zM1058.824 588.235h117.647v117.647h-117.647zM1176.471 588.235h117.647v117.647h-117.647zM1294.118 588.235h117.647v117.647h-117.647zM1411.765 588.235h117.647v117.647h-117.647zM1529.412 588.235h117.647v117.647h-117.647zM1647.059 588.235h117.647v117.647h-117.647zM1764.706 588.235h117.647v117.647h-117.647zM1882.353 588.235H2000v117.647h-117.647zM0 705.882h117.647v117.647H0zM117.647 705.882h117.647v117.647H117.647zM235.294 705.882h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 705.882h117.647v117.647H352.941zM470.588 705.882h117.647v117.647H470.588zM588.235 705.882h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 705.882h117.647v117.647H705.882zM823.529 705.882h117.647v117.647H823.529zM941.176 705.882h117.647v117.647H941.176zM1058.824 705.882h117.647v117.647h-117.647zM1176.471 705.882h117.647v117.647h-117.647zM1294.118 705.882h117.647v117.647h-117.647zM1411.765 705.882h117.647v117.647h-117.647zM1529.412 705.882h117.647v117.647h-117.647zM1647.059 705.882h117.647v117.647h-117.647zM1764.706 705.882h117.647v117.647h-117.647zM1882.353 705.882H2000v117.647h-117.647zM0 823.529h117.647v117.647H0zM117.647 823.529h117.647v117.647H117.647zM235.294 823.529h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 823.529h117.647v117.647H352.941zM470.588 823.529h117.647v117.647H470.588zM588.235 823.529h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 823.529h117.647v117.647H705.882zM823.529 823.529h117.647v117.647H823.529zM941.176 823.529h117.647v117.647H941.176zM1058.824 823.529h117.647v117.647h-117.647zM1176.471 823.529h117.647v117.647h-117.647zM1294.118 823.529h117.647v117.647h-117.647zM1411.765 823.529h117.647v117.647h-117.647zM1529.412 823.529h117.647v117.647h-117.647zM1647.059 823.529h117.647v117.647h-117.647zM1764.706 823.529h117.647v117.647h-117.647zM1882.353 823.529H2000v117.647h-117.647zM0 941.176h117.647v117.647H0zM117.647 941.176h117.647v117.647H117.647zM235.294 941.176h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 941.176h117.647v117.647H352.941zM470.588 941.176h117.647v117.647H470.588zM588.235 941.176h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 941.176h117.647v117.647H705.882zM823.529 941.176h117.647v117.647H823.529zM941.176 941.176h117.647v117.647H941.176zM1058.824 941.176h117.647v117.647h-117.647zM1176.471 941.176h117.647v117.647h-117.647zM1294.118 941.176h117.647v117.647h-117.647zM1411.765 941.176h117.647v117.647h-117.647zM1529.412 941.176h117.647v117.647h-117.647zM1647.059 941.176h117.647v117.647h-117.647zM1764.706 941.176h117.647v117.647h-117.647zM1882.353 941.176H2000v117.647h-117.647zM0 1058.824h117.647v117.647H0zM117.647 1058.824h117.647v117.647H117.647zM235.294 1058.824h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1058.824h117.647v117.647H352.941zM470.588 1058.824h117.647v117.647H470.588zM588.235 1058.824h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1058.824h117.647v117.647H705.882zM823.529 1058.824h117.647v117.647H823.529zM941.176 1058.824h117.647v117.647H941.176zM1058.824 1058.824h117.647v117.647h-117.647zM1176.471 1058.824h117.647v117.647h-117.647zM1294.118 1058.824h117.647v117.647h-117.647zM1411.765 1058.824h117.647v117.647h-117.647zM1529.412 1058.824h117.647v117.647h-117.647zM1647.059 1058.824h117.647v117.647h-117.647zM1764.706 1058.824h117.647v117.647h-117.647zM1882.353 1058.824H2000v117.647h-117.647zM0 1176.471h117.647v117.647H0zM117.647 1176.471h117.647v117.647H117.647zM235.294 1176.471h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1176.471h117.647v117.647H352.941zM470.588 1176.471h117.647v117.647H470.588zM588.235 1176.471h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1176.471h117.647v117.647H705.882zM823.529 1176.471h117.647v117.647H823.529zM941.176 1176.471h117.647v117.647H941.176zM1058.824 1176.471h117.647v117.647h-117.647zM1176.471 1176.471h117.647v117.647h-117.647zM1294.118 1176.471h117.647v117.647h-117.647zM1411.765 1176.471h117.647v117.647h-117.647zM1529.412 1176.471h117.647v117.647h-117.647zM1647.059 1176.471h117.647v117.647h-117.647zM1764.706 1176.471h117.647v117.647h-117.647zM1882.353 1176.471H2000v117.647h-117.647zM0 1294.118h117.647v117.647H0zM117.647 1294.118h117.647v117.647H117.647zM235.294 1294.118h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1294.118h117.647v117.647H352.941zM470.588 1294.118h117.647v117.647H470.588zM588.235 1294.118h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1294.118h117.647v117.647H705.882zM823.529 1294.118h117.647v117.647H823.529zM941.176 1294.118h117.647v117.647H941.176zM1058.824 1294.118h117.647v117.647h-117.647zM1176.471 1294.118h117.647v117.647h-117.647zM1294.118 1294.118h117.647v117.647h-117.647zM1411.765 1294.118h117.647v117.647h-117.647zM1529.412 1294.118h117.647v117.647h-117.647zM1647.059 1294.118h117.647v117.647h-117.647zM1764.706 1294.118h117.647v117.647h-117.647zM1882.353 1294.118H2000v117.647h-117.647z%22%2F%3E%3C%2Fg%3E%3Cdefs%3E%3CradialGradient id=%22a%22%3E%3Cstop offset=%220%22 stop-color=%22%23fff%22%2F%3E%3Cstop offset=%22100%25%22 stop-color=%22%23fff%22 stop-opacity=%220%22%2F%3E%3C%2FradialGradient%3E%3C%2Fdefs%3E%3C%2Fsvg%3E") !important;
            @endif
        }

        @keyframes fadeInBg {
            to {
                opacity: 1;
            }
        }

        #app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div > div > form > div > div.LoginFormContainer___StyledDiv2-sc-cyh04c-4.eyJirO > img {
            @if(!empty($logo_url) && $show_logo_login_enabled)
                content: url("{{ $logo_url }}");
                border-radius: var(--br-default);
                padding: 1rem;
                height: 10rem !important;
                width: 10rem !important;
            @else
                display: none !important;
            @endif
        }

        #app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div > div > form > div > div.LoginFormContainer___StyledDiv2-sc-cyh04c-4.eyJirO {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }
        
        .kVijQB .status-bar {
            display: none !important;
            width: 15px !important;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 20;
            border-radius: 100%;
            margin: 0;
            opacity: 0.90;
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 500ms;
            height: 15px !important;
            background-color: rgba(245 158 11 / 75%) !important;
            border-color: #0000008c;
            border-style: solid;
            border-width: 1px;
    }

    .fRwFrz .status-bar {
            display: none !important;
            width: 15px !important;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 20;
            border-radius: 100%;
            margin: 0;
            opacity: 0.90;
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 500ms;
            height: 15px !important;
            background-color: rgba(16 185 129 / 75%) !important;
            border-color: #0000008c;
            border-style: solid;
            border-width: 1px;
    }

    .fwbDSe .status-bar {
            display: none !important;
            width: 15px !important;
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 20;
            border-radius: 100%;
            margin: 0;
            opacity: 0.90;
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 500ms;
            height: 15px !important;
            background-color: rgba(239 68 68 / 75%) !important;
            border-color: #0000008c;
            border-style: solid;
            border-width: 1px;
    }

        .profile-picture {
        @if(!$profile_picture_url_enabled)
            display: none !important;
        @endif
        }

        .bg-neutral-800 {
            @if(isset($background_url) && !empty($background_url))
                opacity: 0;
                animation: fadeInBg 0.7s ease-in forwards;
                background-image: url('{{ $background_url }}') !important;
                background-attachment: fixed; /* Keeps the background fixed while scrolling */
                background-size: cover; /* Ensures the image covers the whole background */
                background-position: center center; /* Centers the image */
                background-color: hsl(234.09deg 53.37% 3.01% / 97%) !important; /* Fallback background color */
            @else
                opacity: 0;
                background-color: hsl(234.09deg 53.37% 3.01% / 97%) !important; /* Fallback background color */
                background-size: cover;
                animation: fadeInBg 0.7s ease-in forwards;
                background-position: center center;
                background-repeat: repeat;
                background-attachment: fixed;
                background-image: url("data:image/svg+xml;utf8,%3Csvg viewBox=%220 0 2000 1400%22 xmlns=%22http:%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cmask id=%22b%22 x=%220%22 y=%220%22 width=%222000%22 height=%221400%22%3E%3Cpath fill=%22url(%23a)%22 d=%22M0 0h2000v1400H0z%22%2F%3E%3C%2Fmask%3E%3Cpath d=%22M0 0h2000v1400H0z%22%2F%3E%3Cg style=%22transform-origin:center center%22 stroke=%22%23979797%22 stroke-width=%22.2%22 fill=%22none%22 mask=%22url(%23b)%22%3E%3Cpath d=%22M0 0h117.647v117.647H0zM117.647 0h117.647v117.647H117.647zM235.294 0h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 0h117.647v117.647H352.941zM470.588 0h117.647v117.647H470.588zM588.235 0h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 0h117.647v117.647H705.882zM823.529 0h117.647v117.647H823.529zM941.176 0h117.647v117.647H941.176zM1058.824 0h117.647v117.647h-117.647zM1176.471 0h117.647v117.647h-117.647zM1294.118 0h117.647v117.647h-117.647zM1411.765 0h117.647v117.647h-117.647zM1529.412 0h117.647v117.647h-117.647zM1647.059 0h117.647v117.647h-117.647zM1764.706 0h117.647v117.647h-117.647zM1882.353 0H2000v117.647h-117.647zM0 117.647h117.647v117.647H0zM117.647 117.647h117.647v117.647H117.647zM235.294 117.647h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 117.647h117.647v117.647H352.941zM470.588 117.647h117.647v117.647H470.588zM588.235 117.647h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 117.647h117.647v117.647H705.882zM823.529 117.647h117.647v117.647H823.529zM941.176 117.647h117.647v117.647H941.176zM1058.824 117.647h117.647v117.647h-117.647zM1176.471 117.647h117.647v117.647h-117.647zM1294.118 117.647h117.647v117.647h-117.647zM1411.765 117.647h117.647v117.647h-117.647zM1529.412 117.647h117.647v117.647h-117.647zM1647.059 117.647h117.647v117.647h-117.647zM1764.706 117.647h117.647v117.647h-117.647zM1882.353 117.647H2000v117.647h-117.647zM0 235.294h117.647v117.647H0zM117.647 235.294h117.647v117.647H117.647zM235.294 235.294h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 235.294h117.647v117.647H352.941zM470.588 235.294h117.647v117.647H470.588zM588.235 235.294h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 235.294h117.647v117.647H705.882zM823.529 235.294h117.647v117.647H823.529zM941.176 235.294h117.647v117.647H941.176zM1058.824 235.294h117.647v117.647h-117.647zM1176.471 235.294h117.647v117.647h-117.647zM1294.118 235.294h117.647v117.647h-117.647zM1411.765 235.294h117.647v117.647h-117.647zM1529.412 235.294h117.647v117.647h-117.647zM1647.059 235.294h117.647v117.647h-117.647zM1764.706 235.294h117.647v117.647h-117.647zM1882.353 235.294H2000v117.647h-117.647zM0 352.941h117.647v117.647H0zM117.647 352.941h117.647v117.647H117.647zM235.294 352.941h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 352.941h117.647v117.647H352.941zM470.588 352.941h117.647v117.647H470.588zM588.235 352.941h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 352.941h117.647v117.647H705.882zM823.529 352.941h117.647v117.647H823.529zM941.176 352.941h117.647v117.647H941.176zM1058.824 352.941h117.647v117.647h-117.647zM1176.471 352.941h117.647v117.647h-117.647zM1294.118 352.941h117.647v117.647h-117.647zM1411.765 352.941h117.647v117.647h-117.647zM1529.412 352.941h117.647v117.647h-117.647zM1647.059 352.941h117.647v117.647h-117.647zM1764.706 352.941h117.647v117.647h-117.647zM1882.353 352.941H2000v117.647h-117.647zM0 470.588h117.647v117.647H0zM117.647 470.588h117.647v117.647H117.647zM235.294 470.588h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 470.588h117.647v117.647H352.941zM470.588 470.588h117.647v117.647H470.588zM588.235 470.588h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 470.588h117.647v117.647H705.882zM823.529 470.588h117.647v117.647H823.529zM941.176 470.588h117.647v117.647H941.176zM1058.824 470.588h117.647v117.647h-117.647zM1176.471 470.588h117.647v117.647h-117.647zM1294.118 470.588h117.647v117.647h-117.647zM1411.765 470.588h117.647v117.647h-117.647zM1529.412 470.588h117.647v117.647h-117.647zM1647.059 470.588h117.647v117.647h-117.647zM1764.706 470.588h117.647v117.647h-117.647zM1882.353 470.588H2000v117.647h-117.647zM0 588.235h117.647v117.647H0zM117.647 588.235h117.647v117.647H117.647zM235.294 588.235h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 588.235h117.647v117.647H352.941zM470.588 588.235h117.647v117.647H470.588zM588.235 588.235h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 588.235h117.647v117.647H705.882zM823.529 588.235h117.647v117.647H823.529zM941.176 588.235h117.647v117.647H941.176zM1058.824 588.235h117.647v117.647h-117.647zM1176.471 588.235h117.647v117.647h-117.647zM1294.118 588.235h117.647v117.647h-117.647zM1411.765 588.235h117.647v117.647h-117.647zM1529.412 588.235h117.647v117.647h-117.647zM1647.059 588.235h117.647v117.647h-117.647zM1764.706 588.235h117.647v117.647h-117.647zM1882.353 588.235H2000v117.647h-117.647zM0 705.882h117.647v117.647H0zM117.647 705.882h117.647v117.647H117.647zM235.294 705.882h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 705.882h117.647v117.647H352.941zM470.588 705.882h117.647v117.647H470.588zM588.235 705.882h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 705.882h117.647v117.647H705.882zM823.529 705.882h117.647v117.647H823.529zM941.176 705.882h117.647v117.647H941.176zM1058.824 705.882h117.647v117.647h-117.647zM1176.471 705.882h117.647v117.647h-117.647zM1294.118 705.882h117.647v117.647h-117.647zM1411.765 705.882h117.647v117.647h-117.647zM1529.412 705.882h117.647v117.647h-117.647zM1647.059 705.882h117.647v117.647h-117.647zM1764.706 705.882h117.647v117.647h-117.647zM1882.353 705.882H2000v117.647h-117.647zM0 823.529h117.647v117.647H0zM117.647 823.529h117.647v117.647H117.647zM235.294 823.529h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 823.529h117.647v117.647H352.941zM470.588 823.529h117.647v117.647H470.588zM588.235 823.529h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 823.529h117.647v117.647H705.882zM823.529 823.529h117.647v117.647H823.529zM941.176 823.529h117.647v117.647H941.176zM1058.824 823.529h117.647v117.647h-117.647zM1176.471 823.529h117.647v117.647h-117.647zM1294.118 823.529h117.647v117.647h-117.647zM1411.765 823.529h117.647v117.647h-117.647zM1529.412 823.529h117.647v117.647h-117.647zM1647.059 823.529h117.647v117.647h-117.647zM1764.706 823.529h117.647v117.647h-117.647zM1882.353 823.529H2000v117.647h-117.647zM0 941.176h117.647v117.647H0zM117.647 941.176h117.647v117.647H117.647zM235.294 941.176h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 941.176h117.647v117.647H352.941zM470.588 941.176h117.647v117.647H470.588zM588.235 941.176h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 941.176h117.647v117.647H705.882zM823.529 941.176h117.647v117.647H823.529zM941.176 941.176h117.647v117.647H941.176zM1058.824 941.176h117.647v117.647h-117.647zM1176.471 941.176h117.647v117.647h-117.647zM1294.118 941.176h117.647v117.647h-117.647zM1411.765 941.176h117.647v117.647h-117.647zM1529.412 941.176h117.647v117.647h-117.647zM1647.059 941.176h117.647v117.647h-117.647zM1764.706 941.176h117.647v117.647h-117.647zM1882.353 941.176H2000v117.647h-117.647zM0 1058.824h117.647v117.647H0zM117.647 1058.824h117.647v117.647H117.647zM235.294 1058.824h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1058.824h117.647v117.647H352.941zM470.588 1058.824h117.647v117.647H470.588zM588.235 1058.824h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1058.824h117.647v117.647H705.882zM823.529 1058.824h117.647v117.647H823.529zM941.176 1058.824h117.647v117.647H941.176zM1058.824 1058.824h117.647v117.647h-117.647zM1176.471 1058.824h117.647v117.647h-117.647zM1294.118 1058.824h117.647v117.647h-117.647zM1411.765 1058.824h117.647v117.647h-117.647zM1529.412 1058.824h117.647v117.647h-117.647zM1647.059 1058.824h117.647v117.647h-117.647zM1764.706 1058.824h117.647v117.647h-117.647zM1882.353 1058.824H2000v117.647h-117.647zM0 1176.471h117.647v117.647H0zM117.647 1176.471h117.647v117.647H117.647zM235.294 1176.471h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1176.471h117.647v117.647H352.941zM470.588 1176.471h117.647v117.647H470.588zM588.235 1176.471h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1176.471h117.647v117.647H705.882zM823.529 1176.471h117.647v117.647H823.529zM941.176 1176.471h117.647v117.647H941.176zM1058.824 1176.471h117.647v117.647h-117.647zM1176.471 1176.471h117.647v117.647h-117.647zM1294.118 1176.471h117.647v117.647h-117.647zM1411.765 1176.471h117.647v117.647h-117.647zM1529.412 1176.471h117.647v117.647h-117.647zM1647.059 1176.471h117.647v117.647h-117.647zM1764.706 1176.471h117.647v117.647h-117.647zM1882.353 1176.471H2000v117.647h-117.647zM0 1294.118h117.647v117.647H0zM117.647 1294.118h117.647v117.647H117.647zM235.294 1294.118h117.647v117.647H235.294z%22%2F%3E%3Cpath d=%22M352.941 1294.118h117.647v117.647H352.941zM470.588 1294.118h117.647v117.647H470.588zM588.235 1294.118h117.647v117.647H588.235z%22%2F%3E%3Cpath d=%22M705.882 1294.118h117.647v117.647H705.882zM823.529 1294.118h117.647v117.647H823.529zM941.176 1294.118h117.647v117.647H941.176zM1058.824 1294.118h117.647v117.647h-117.647zM1176.471 1294.118h117.647v117.647h-117.647zM1294.118 1294.118h117.647v117.647h-117.647zM1411.765 1294.118h117.647v117.647h-117.647zM1529.412 1294.118h117.647v117.647h-117.647zM1647.059 1294.118h117.647v117.647h-117.647zM1764.706 1294.118h117.647v117.647h-117.647zM1882.353 1294.118H2000v117.647h-117.647z%22%2F%3E%3C%2Fg%3E%3Cdefs%3E%3CradialGradient id=%22a%22%3E%3Cstop offset=%220%22 stop-color=%22%23fff%22%2F%3E%3Cstop offset=%22100%25%22 stop-color=%22%23fff%22 stop-opacity=%220%22%2F%3E%3C%2FradialGradient%3E%3C%2Fdefs%3E%3C%2Fsvg%3E") !important;
            @endif
        }

        @if($side_nav_enabled)
            #SubNavigation > div:nth-child(1),
            #NavigationBarRemake {
                display: none !important; /* Use regular styles, avoid !important */
            }

            #CustomButtons {
                display: flex !important;
                z-index: 0 !important;
                position: relative;
            }

            .announcement-container {
                    margin-right: 0 !important;
                    margin-left: 10% !important;
            }

            #app {
                margin-left: 10% !important; /* Reset margin for the main app container */
            }

            @media (max-width: 1540px) {
                #app {
                    margin-left: 0 !important; /* Remove margin for smaller screens */
                }

                .announcement-container {
                    margin-right: 1rem !important;
                    margin-left: 1rem !important;
                }
            }
        @else
            #CustomButtons,
            #app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > div.sideNavDashboard__SideNavigation-sc-kxfwvj-0.hZqHvJ,
            #SideNavigation {
                display: none !important; /* Use regular styles, avoid !important */
            }

            @media (max-width: 1540px) {
                #SubNavigation > div:nth-child(1),
                #NavigationBarRemake {
                    display: flex !important; /* Ensure proper rendering for smaller screens */
                }
            }
        @endif

#app > div.App___StyledDiv-sc-2l91w7-0.fnfeQw > div.Fade__Container-sc-1p0gm8n-0.hcgQjy > section > div.ContentContainer-sc-x3r2dw-0.PageContentBlock___StyledContentContainer-sc-kbxq2g-0.jyeSuy.HeRWk.fade-appear-done.fade-enter-done > a {
    position: relative; /* Required for absolute positioning of the background image div */
    overflow: hidden; /* Ensure no overflow */
}

.background-image {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 0; /* Place it behind any content */
}

    </style>
</head>

@if(Auth::check())
<div id="cookie-alert" style="display: none;" class="cookie-alert">
    <i class="fa-solid fa-cookie-bite"></i> This site uses cookies to enhance your experience. 
    <button id="accept-cookies" style="background: #C2185B; border: none; color: white; padding: 10px; margin-left: 10px; cursor: pointer;">Accept</button>
</div>

@if(request()->is('/')) <!-- Check if current route is the homepage -->
    @if($announcement_type !== 'disable')
        <div class="announcement-wrapper" style="display: none;"> <!-- Initially hidden, JavaScript controls visibility -->
            <div class="announcement-container {{ $announcement_type }}">
                <div class="announcement-icon">
                    <i class="{{ $announcement_icon }}"></i>
                </div>
                <div class="announcement-content">
                    <p>{{ $announcement_content }}</p>
                </div>
                <button id="announcement-close-btn" class="announcement-close-btn">&times;</button>
            </div>
        </div>
    @else
        <div class="announcement-wrapper" style="display: none;"></div> <!-- Hide wrapper when announcement type is 'disable' -->
    @endif
@endif
@endif