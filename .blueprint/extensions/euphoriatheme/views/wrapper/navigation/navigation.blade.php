<?php
    $site_name_enabled = $blueprint->dbGet("euphoriatheme", 'site_name_enabled');
?>

@if(Auth::check())

<div class="w-full shadow-md overflow-visible z-20" id="NavigationBarRemake">
    <div class="Fade__Container-sc-1p0gm8n-0 hcgQjy"></div>
    <div class="mx-auto w-full flex flex-col items-center max-w-[1200px]">
        <!-- Top Row: Logo, Search Bar, Navigation -->
        <div class="w-full flex items-center h-[3.5rem]">
            <!-- Logo Section -->
            <div id="logo" class="flex-1 h-[3.5rem] flex items-center">
                <a href="/" class="flex items-center">
                    <img src="{{ $logo_url }}" style="height: 3.5rem;">
                    @if($site_name_enabled)
                    <span class="ml-3 text-lg font-bold text-white">
                        {{ old('app:name', config('app.name')) }}
                    </span>
                    @endif
                </a>
            </div>

            <!-- Search Bar -->
            <div class="flex-1 flex justify-center relative z-50">
                <input type="text" id="searchBar" placeholder="Search..." autocomplete="off" class="w-full px-4 py-2 rounded-md border border-gray-600 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <ul id="searchResults" class="absolute mt-12 w-full bg-gray-800 text-white border border-gray-600 rounded-md hidden max-h-12 overflow-y-auto z-50" style="max-height: 3rem; overflow-y: auto; margin-top: 3rem;"></ul>
            </div>

            <!-- Right Side Navigation -->
            <div class="flex-1 NavigationBar__RightNavigation-sc-tupl2x-0 navbar flex h-full items-center justify-center">
                <a aria-current="page"
                    class="{{ Request::is('/') ? 'active' : '' }}"
                    id="NavigationDashboard" href="/">
                    <i class="fa-solid fa-house"></i>
                </a>
                @if(Auth::user()->root_admin)
                    <a href="/admin" rel="noreferrer" id="NavigationAdmin"
                        class="{{ Request::is('admin') ? 'active' : '' }} navigation-button">
                            <i class="fa-solid fa-gears"></i>
                    </a>
                @endif
                <a id="NavigationAccount" href="/account" class="{{ Request::is('account') ? 'active' : '' }} navigation-button">
                    <span class="flex items-center w-10 h-10">
                        @if(Auth::user()->profile_picture_url)
                            <img src="{{ Auth::user()->profile_picture_url }}" style="border-radius: 50%; height: 40px !important; width: 40px !important" alt="User Image">
                        @else
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" style="border-radius: 50%; height: 40px !important; width: 40px !important"  alt="User Image">
                        @endif
                    </span>
                </a>
                <form action="/auth/logout" method="POST">
                    @csrf
                    <button id="NavigationLogout" type="submit" class="navigation-button" style="border: rgba(255, 255, 255, 0.07843);border-style: solid;border-width: 2.5px !important;border-radius: var(--br-default);">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Row: Custom Buttons Centered -->
        <div class="w-full flex justify-center mt-2">
            <div class="flex space-x-4 custom-buttons">
                @if(strlen($store_url) > 5)
                <a href="{{ $store_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button">
                    <i class="fa-solid fa-store mr-2"></i> Store
                </a>
                @endif
                @if(strlen($status_url) > 5)
                <a href="{{ $status_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button">
                    <i class="fa-solid fa-signal mr-2"></i> Status
                </a>
                @endif
                @if(strlen($discord_url) > 5)
                <a href="{{ $discord_url }}" target="_blank" rel="noreferrer" id="NavigationLink" class="navigation-button">
                    <i class="fa-brands fa-discord mr-2"></i> Discord
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    const searchBar = document.getElementById('searchBar');
    const searchResults = document.getElementById('searchResults');

    async function fetchServers() {
        try {
            const response = await fetch('/api/client?page=1', {
                headers: {
                    'Authorization': 'Bearer {{ Auth::user()->api_token }}',  // Use dynamic token
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            return data.data.map(server => ({
                name: server.attributes.name,
                id: server.attributes.identifier
            }));
        } catch (error) {
            console.error('Error fetching servers:', error);
            return [];
        }
    }

    searchBar.addEventListener('input', async function() {
        const query = searchBar.value.toLowerCase();
        searchResults.innerHTML = '';

        if (query.length > 0) {
            const servers = await fetchServers();
            const filtered = servers.filter(server => server.name.toLowerCase().includes(query));

            filtered.forEach(server => {
                const li = document.createElement('li');
                li.classList.add('px-4', 'py-2', 'hover:bg-gray-700', 'cursor-pointer');
                li.textContent = server.name;
                li.onclick = () => window.location.href = `/server/${server.id}`;
                searchResults.appendChild(li);
            });

            searchResults.classList.remove('hidden');
        } else {
            searchResults.classList.add('hidden');
        }
    });
</script>
@endif
