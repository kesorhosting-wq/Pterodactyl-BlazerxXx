<?php
    $side_nav_enabled = $blueprint->dbGet("euphoriatheme", 'side_nav_enabled');
    // If custom buttons are configured to show in the side nav, do not render the page-centered custom buttons.
    $custom_in_side_nav = $blueprint->dbGet('euphoriatheme', 'custom_buttons_in_side_nav');
    // Get the custom buttons array
    $custom_buttons = json_decode($blueprint->dbGet('euphoriatheme', 'custom_buttons', '[]'), true) ?? [];
?>

@if(Auth::check() && !(bool) $custom_in_side_nav)

<div class="flex justify-center w-full" id="CustomButtons" style="margin: 0 auto; box-sizing: border-box; z-index: 0;">
    <div class="w-full max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Custom Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 custom-buttons justify-center items-center">
                <div class="flex flex-wrap gap-4 sm:gap-4 justify-center items-center w-full sm:w-auto">
                    @if(strlen($store_url) > 5)
                    <a href="{{ $store_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button flex-1 sm:flex-initial min-w-[120px] sm:min-w-[140px] flex items-center justify-center mb-2 sm:mb-0">
                        <i class="fa-solid fa-store mr-2"></i> 
                        <span class="text-sm sm:text-base">Store</span>
                    </a>
                    @endif
                    
                    @if(strlen($status_url) > 5)
                    <a href="{{ $status_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button flex-1 sm:flex-initial min-w-[120px] sm:min-w-[140px] flex items-center justify-center mb-2 sm:mb-0">
                        <i class="fa-solid fa-signal mr-2"></i> 
                        <span class="text-sm sm:text-base">Status</span>
                    </a>
                    @endif
                    
                    @if(strlen($discord_url) > 5)
                    <a href="{{ $discord_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button flex-1 sm:flex-initial min-w-[120px] sm:min-w-[140px] flex items-center justify-center mb-2 sm:mb-0">
                        <i class="fa-brands fa-discord mr-2"></i> 
                        <span class="text-sm sm:text-base">Discord</span>
                    </a>
                    @endif

                    {{-- Additional custom buttons from Theme Customiser --}}
                    @if(is_array($custom_buttons) && count($custom_buttons) > 0)
                        @foreach($custom_buttons as $button)
                            @if(isset($button['url']) && isset($button['title']) && strlen($button['url']) > 3 && strlen($button['title']) > 0)
                                <a href="{{ $button['url'] }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button flex-1 sm:flex-initial min-w-[120px] sm:min-w-[140px] flex items-center justify-center mb-2 sm:mb-0">
                                    @if(isset($button['icon']) && strlen($button['icon']) > 0)
                                        <i class="{{ $button['icon'] }} mr-2"></i>
                                    @else
                                        <i class="fa-solid fa-link mr-2"></i>
                                    @endif
                                    <span class="text-sm sm:text-base">{{ $button['title'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    @endif
            </div>
        </div>
    </div>
</div>

@endif