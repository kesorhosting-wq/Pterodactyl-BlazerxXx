@extends('layouts.admin')

@section('title', 'Translations')

@section('content-header')
    <h1>Translations<small>View & Manage Translations</small></h1>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <strong><i class="fa fa-language"></i> Manage Languages</strong>
                </h3>
                <p class="box-subtitle">Select the languages you want to enable for your Panel.</p>
            </div>
            <div class="box-body">
                <ul id="language-list" style="list-style: none; padding: 0;">
                    <!-- Languages will be dynamically populated here -->
                </ul>
                <button id="save-language-settings" class="btn btn-primary">Save Settings</button>
            </div>
            <div class="form-group">
                <label for="default-language">Default Language</label>
                <select id="default-language" class="form-control">
                    <!-- Populate this dropdown dynamically with available languages -->
                </select>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const languageList = document.getElementById('language-list');
    const defaultLanguageDropdown = document.getElementById('default-language');
    const saveButton = document.getElementById('save-language-settings');

    // Use configured language API base URL if provided by the server, otherwise fall back to the public API
    // The stored setting is expected to be a base URL (e.g. 'http://host:2002'), so append '/translations/' if missing.
    (function(){
        const raw = {!! json_encode($language_api_url ?? '') !!};
        if (raw && raw.trim().length > 0) {
            // Remove trailing slash if present, then append /translations/
            const normalized = raw.replace(/\/+$/,'') + '/translations/';
            window.__EUPHORIA_LANGUAGE_API = normalized;
        } else {
            window.__EUPHORIA_LANGUAGE_API = 'https://api.euphoriadevelopment.uk/translations/';
        }
    })();
    const languageApiUrl = window.__EUPHORIA_LANGUAGE_API;

    // Fetch available languages and current settings
    const fetchLanguages = async () => {
        try {
            const response = await fetch(languageApiUrl, { method: 'GET' });
            if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
            const data = await response.json();
            return data.languages || [];
        } catch (e) {
            console.warn('Failed to fetch languages from', languageApiUrl, e);
            return []; // Fail gracefully and return empty list
        }
    };

    const fetchLanguageSettings = async () => {
        const response = await fetch('/extensions/euphoriatheme/admin/languages/settings', { method: 'GET' });
        const data = await response.json();
        return {
            enabledLanguages: data.enabledLanguages || [],
            defaultLanguage: data.defaultLanguage || 'en', // Default to English if not set
        };
    };

    // Populate the language list and default language dropdown
    const populateLanguageList = async () => {
        const languages = await fetchLanguages();
        const { enabledLanguages, defaultLanguage } = await fetchLanguageSettings();

        // Populate the language list
        languageList.innerHTML = ''; // Clear existing items
        languages.forEach(lang => {
            const listItem = document.createElement('li');
            listItem.style.padding = '5px 0';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = enabledLanguages.includes(lang.code);
            checkbox.setAttribute('data-lang', lang.code);

            const label = document.createElement('label');
            label.textContent = ` ${lang.name}`;
            label.style.marginLeft = '10px'; // Add a gap between the checkbox and the label

            listItem.appendChild(checkbox);
            listItem.appendChild(label);
            languageList.appendChild(listItem);
        });

        // Populate the default language dropdown
        defaultLanguageDropdown.innerHTML = ''; // Clear existing options
        languages.forEach(lang => {
            const option = document.createElement('option');
            option.value = lang.code;
            option.textContent = lang.name;
            if (lang.code === defaultLanguage) {
                option.selected = true; // Mark the default language as selected
            }
            defaultLanguageDropdown.appendChild(option);
        });

        // Ensure English ("en") is always an option
        if (!languages.some(lang => lang.code === 'en')) {
            const englishOption = document.createElement('option');
            englishOption.value = 'en';
            englishOption.textContent = 'English';
            if (defaultLanguage === 'en') {
                englishOption.selected = true;
            }
            defaultLanguageDropdown.appendChild(englishOption);
        }
    };

    // Save language settings
    saveButton.addEventListener('click', async () => {
        const checkboxes = languageList.querySelectorAll('input[type="checkbox"]');
        const enabledLanguages = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.getAttribute('data-lang'));

        const defaultLanguage = defaultLanguageDropdown.value;

        const csrfToken = document.querySelector('meta[name="_token"]').getAttribute('content'); // Get CSRF token

        const response = await fetch('/extensions/euphoriatheme/admin/languages/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken, // Include CSRF token in the headers
            },
            body: JSON.stringify({ languages: enabledLanguages, defaultLanguage }),
        });

        const data = await response.json();
        if (data.success) {
            alert('Language settings saved successfully!');
        } else {
            alert('Failed to save language settings.');
        }
    });

    // Initialize the language list and dropdown
    await populateLanguageList();
});
</script>
@endsection