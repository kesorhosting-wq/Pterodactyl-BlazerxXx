@if(Auth::check())
<?php
    $loading_screen_enabled = $blueprint->dbGet("euphoriatheme", 'loading_screen_enabled');   
    $loading_screen_background_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_background_url');
    $loading_screen_logo_url = $blueprint->dbGet("euphoriatheme", 'loading_screen_logo_url');
?>

<!-- Loading Screen Element -->
@if($loading_screen_enabled && ($loading_screen_logo_url || $loading_screen_background_url))
<div id="loading-screen" class="loading-screen" style="display: none;">
    @if($loading_screen_logo_url)
    <img id="loading-logo" src="{{ $loading_screen_logo_url }}" alt="Loading Logo" class="loading-logo">
    @endif
</div>
<style>
/* Loading Screen Styles */
    #loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(135deg, rgba(15, 15, 25, 0.95), rgba(25, 25, 35, 0.95)); /* Default dark glass background */
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        background-size: cover;
        background-position: center center;
        display: none; /* Hidden initially */
        justify-content: center;
        align-items: center;
        z-index: 9999; /* Ensure it's on top */
        opacity: 1;
        transition: opacity 1s ease-out;
    }

    /* Fade out class for smooth transition */
    #loading-screen.fade-out {
        opacity: 0;
    }


/* Loading Logo Animation */
.loading-logo {
    width: 150px; /* Adjust the size as needed */
    height: auto;
    animation: spin 5s linear infinite;
    opacity: 1;
    transition: opacity 0.8s ease-out;
}

/* Logo fade out */
.loading-logo.fade-out {
    opacity: 0;
}

/* Spinning Animation */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function showLoadingScreen() {
        const loadingScreen = document.getElementById('loading-screen');
        if (!loadingScreen) return;

        const loadingMode = '{{ $loading_screen_mode ?? 'timer' }}';
        const loadingDuration = parseInt('{{ $loading_screen_duration ?? 4 }}') * 1000; // Convert to milliseconds

        // Set the background image if provided, otherwise keep the default dark glass background
        const loadingScreenBackgroundUrl = '{{ $loading_screen_background_url }}';
        if (loadingScreenBackgroundUrl) {
            loadingScreen.style.backgroundImage = `url('${loadingScreenBackgroundUrl}')`;
            loadingScreen.style.backgroundSize = 'cover';
            loadingScreen.style.backgroundPosition = 'center center';
            // Remove backdrop filter when using custom background
            loadingScreen.style.backdropFilter = 'none';
            loadingScreen.style.webkitBackdropFilter = 'none';
        } else {
            // Ensure dark glass background is maintained when no custom background
            loadingScreen.style.background = 'linear-gradient(135deg, rgba(15, 15, 25, 0.95), rgba(25, 25, 35, 0.95))';
            loadingScreen.style.backdropFilter = 'blur(20px)';
            loadingScreen.style.webkitBackdropFilter = 'blur(20px)';
        }

        loadingScreen.style.display = 'flex';

        function hideLoadingScreen() {
            const loadingLogo = document.getElementById('loading-logo');
            
            // First fade out the logo
            if (loadingLogo) {
                loadingLogo.classList.add('fade-out');
            }
            
            // Then fade out the background after logo starts fading
            setTimeout(() => {
                loadingScreen.classList.add('fade-out');
                
                // Finally hide the element completely after fade animation
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                    loadingScreen.classList.remove('fade-out');
                    if (loadingLogo) {
                        loadingLogo.classList.remove('fade-out');
                    }
                }, 1000); // Wait for fade-out animation to complete
            }, 300); // Small delay between logo and background fade
        }

        if (loadingMode === 'timer') {
            // Timer-based loading screen
            setTimeout(hideLoadingScreen, loadingDuration);
        } else if (loadingMode === 'page_load') {
            // Page load-based loading screen
            let pageLoaded = false;
            let imagesLoaded = false;
            
            // Check if page content is loaded
            if (document.readyState === 'complete') {
                pageLoaded = true;
            } else {
                window.addEventListener('load', () => {
                    pageLoaded = true;
                    if (imagesLoaded) hideLoadingScreen();
                });
            }
            
            // Wait for all images to load
            const images = document.querySelectorAll('img');
            let loadedImages = 0;
            
            if (images.length === 0) {
                imagesLoaded = true;
                if (pageLoaded) hideLoadingScreen();
            } else {
                images.forEach(img => {
                    if (img.complete) {
                        loadedImages++;
                    } else {
                        img.addEventListener('load', () => {
                            loadedImages++;
                            if (loadedImages === images.length) {
                                imagesLoaded = true;
                                if (pageLoaded) hideLoadingScreen();
                            }
                        });
                        img.addEventListener('error', () => {
                            loadedImages++;
                            if (loadedImages === images.length) {
                                imagesLoaded = true;
                                if (pageLoaded) hideLoadingScreen();
                            }
                        });
                    }
                });
                
                // Check if all images were already loaded
                if (loadedImages === images.length) {
                    imagesLoaded = true;
                    if (pageLoaded) hideLoadingScreen();
                }
            }
            
            // Fallback timeout to prevent infinite loading (max 30 seconds)
            setTimeout(hideLoadingScreen, 30000);
        }
    }

    const currentPath = window.location.pathname;
    if (!currentPath.startsWith('/admin') && !currentPath.startsWith('/auth') && !currentPath.startsWith('/server') && !currentPath.startsWith('/account') && {{ $loading_screen_enabled && ($loading_screen_logo_url || $loading_screen_background_url) ? 'true' : 'false' }}) {
        showLoadingScreen();
    }
});
</script>
@endif
@endif