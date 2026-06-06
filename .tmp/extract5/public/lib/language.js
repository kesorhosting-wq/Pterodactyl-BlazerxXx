document.addEventListener('DOMContentLoaded', async function () {
    const button = document.getElementById('language-selector-button');
    const dropdown = document.getElementById('language-dropdown');
    const tooltip = document.getElementById('language-tooltip');

    // Get custom API URL or default
    const getApiUrl = () => {
        const siteConfig = window.SiteConfiguration || {};
        const customUrl = siteConfig.language_api_url;
        return customUrl && customUrl.trim() !== '' ? customUrl.replace(/\/$/, '') : 'https://api.euphoriadevelopment.uk';
    };

    // Fetch available translations from the API
    const fetchAvailableTranslations = async () => {
        try {
            const apiUrl = getApiUrl();
            const response = await fetch(`${apiUrl}/translations/`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();
            if (data.success) {
                return data.languages;
            } else {
                console.error('Failed to fetch available translations:', data.error);
                return [];
            }
        } catch (error) {
            console.error('Error fetching available translations:', error);
            return [];
        }
    };

    const populateDropdown = async () => {
        const languages = await fetchAvailableTranslations();
    
        const response = await fetch('/extensions/euphoriatheme/admin/languages/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
    
        const data = await response.json();
        const enabledLanguages = data.enabledLanguages || [];
        const defaultLanguage = data.defaultLanguage || 'en'; // Use 'en' as fallback if no default is set
    
        // If no enabled languages, default to English and hide the container
        if (enabledLanguages.length === 0) {
            document.getElementById('language-selector-container').style.display = 'none';
            localStorage.setItem('selectedLanguage', 'en'); // Default to English
            return;
        }
    
        dropdown.innerHTML = ''; // Clear existing items
    
        // Ensure English ("en") is always included in the dropdown
        const englishOption = {
            code: 'en',
            name: 'English',
        };
    
        // Add English to the dropdown first
        const englishListItem = document.createElement('li');
        englishListItem.style.padding = '5px 10px';
        englishListItem.style.cursor = 'pointer';
        englishListItem.style.listStyle = 'none';
        englishListItem.setAttribute('data-lang', englishOption.code);
        englishListItem.textContent = englishOption.name;
    
        // Highlight the default language if it's English
        if (defaultLanguage === 'en') {
            englishListItem.style.fontWeight = 'bold';
        }
    
        dropdown.appendChild(englishListItem);
    
        // Add click event for English
        englishListItem.addEventListener('click', () => {
            localStorage.setItem('selectedLanguage', 'en'); // Save English as the selected language
            location.reload(); // Reload the page to apply the language change
        });
    
        // Add all other enabled languages to the dropdown
        languages.forEach(lang => {
            if (lang.code === 'en' || !enabledLanguages.includes(lang.code)) return; // Skip English (already added) and disabled languages
    
            const listItem = document.createElement('li');
            listItem.style.padding = '5px 10px';
            listItem.style.cursor = 'pointer';
            listItem.style.listStyle = 'none';
            listItem.setAttribute('data-lang', lang.code);
            listItem.textContent = lang.name;
    
            // Highlight the default language
            if (lang.code === defaultLanguage) {
                listItem.style.fontWeight = 'bold'; // Highlight the default language
            }
    
            dropdown.appendChild(listItem);
    
            // Add click event to each language item
            listItem.addEventListener('click', () => {
                localStorage.setItem('selectedLanguage', lang.code); // Save selected language
                location.reload(); // Reload the page to apply the language change
            });
        });
    
        // Set the default language in localStorage if no language is selected
        const selectedLanguage = localStorage.getItem('selectedLanguage');
        if (!selectedLanguage) {
            localStorage.setItem('selectedLanguage', defaultLanguage);
        }
    };

    // Show tooltip with current language
    const showTooltip = () => {
        const currentLanguage = localStorage.getItem('selectedLanguage') || 'en';
        const languageText = Array.from(dropdown.querySelectorAll('li')).find(item => item.getAttribute('data-lang') === currentLanguage)?.textContent || 'English';
        tooltip.textContent = `${languageText}`;
        tooltip.style.display = 'block';
        tooltip.style.opacity = '1';

        // Fade out after 5 seconds
        setTimeout(() => {
            tooltip.style.opacity = '0';
            setTimeout(() => {
                tooltip.style.display = 'none';
            }, 500); // Wait for fade-out transition
        }, 5000);
    };

    // Toggle dropdown visibility
    button.addEventListener('click', () => {
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (event) => {
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Populate the dropdown and show tooltip on page load
    await populateDropdown();
    showTooltip();
});