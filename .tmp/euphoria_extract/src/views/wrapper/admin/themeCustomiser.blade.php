<head>
<title> Euphoria Customiser </title>
<style>
:root {
    /* Yellow Colors (Warning) */
    --yellow: rgba(255, 179, 0, 1);

    /* Green Colors (Success) */
    --green: rgba(0, 255, 0, 0.5);

    /* Red Colors (Error) */
    --red: rgba(255, 0, 0, 0.5);
    --white: rgba(255, 255, 255, 1);
    --transparent: transparent;
    --gray-900: hsla(0, 0%, 10%, 1); 
    --gray-700: hsla(0, 0%, 20%, 1);
    --gray-300: hsla(0, 0%, 50%, 1);
    --primary-color: {{ $primary_color }};
}

/* Scrollbar */
::-webkit-scrollbar {
    width: 8px !important;
    height: 10px !important;
}

::-webkit-scrollbar-corner {
    background-color: var(--transparent) !important;
}

::-webkit-scrollbar-track {
    background-color: var(--neutral-900) !important; /* Set this to a dark color or a color of your choice */
}

::-webkit-scrollbar-thumb {
    background-color: var(--gray-700) !important; /* Set the thumb color */
    border-radius: var(--br-default); /* Optional: to round the thumb */
    box-shadow: var(--transparent) 0px 0px 0px 0px inset, var(--transparent) 0px 0px 0px 0px inset !important;
}

body, html {
    margin: 0;
    padding: 0;
    height: 100vh;
    overflow: hidden;
    background-color: var(--gray-900);
    font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

/* Main Layout Container */
.main-container {
    display: grid;
    grid-template-areas: 
        "exit exit"
        "settings preview";
    grid-template-rows: 60px 1fr;
    grid-template-columns: 480px 1fr;
    height: 100vh;
    gap: 0;
}

/* Exit Button Area */
.exit-area {
    grid-area: exit;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 10px 15px;
    background: linear-gradient(135deg, rgba(20, 20, 30, 0.95), rgba(30, 30, 40, 0.95));
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

/* Settings Area */
.settings-area {
    grid-area: settings;
    background: linear-gradient(135deg, rgba(15, 15, 25, 0.95), rgba(25, 25, 35, 0.95));
    border-right: 2px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    backdrop-filter: blur(5px);
}

/* Preview Area */
.preview-area {
    grid-area: preview;
    background: rgba(10, 10, 15, 0.8);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Save Button Centering */
.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 20px;
    padding: 12px 24px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(99, 102, 241, 0.3));
    color: var(--white);
    font-weight: bold;
    border: 2px solid rgba(59, 130, 246, 0.6);
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-align: center;
    width: calc(100% - 40px);
    font-size: 1rem;
    letter-spacing: 0.5px;
}

/* Hover Effects */
.btn-primary:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(99, 102, 241, 0.5));
    border-color: rgba(59, 130, 246, 0.9);
    box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
    transform: translateY(-2px);
    color: var(--white);
}

/* Icon inside the button */
.btn-primary i {
    margin-right: 8px; /* Add spacing between icon and text */
    font-size: 1.2em; /* Slightly larger icon */
    vertical-align: middle;
}

/* Settings Container */
#settings-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

body > div > div.exit-area > button {
    margin-top: 0 !important;
    width: auto !important;
}

/* Form Container */
.form-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    scrollbar-width: thin;
    scrollbar-color: var(--gray-700) var(--gray-900);
}

.form-container::-webkit-scrollbar {
    width: 8px;
}

.form-container::-webkit-scrollbar-track {
    background: var(--gray-900);
}

.form-container::-webkit-scrollbar-thumb {
    background: var(--gray-700);
    border-radius: 4px;
}

/* Save Button Container */
.save-container {
    padding: 0px;
    border-top: 2px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(20, 20, 30, 0.8), rgba(30, 30, 40, 0.8));
    backdrop-filter: blur(10px);
}

#save {
    display: flex;
    justify-content: center;
}

input[type="text" i] {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    color: #ffffff;
    background-color: var(--gray-900);
    border-radius: var(--br-default);
    border-color: #ffffff45;
    border-style: solid;
    border-width: 2px;
}

#live-preview {
    flex: 1;
    margin: 20px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
    border: 2px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(40, 40, 50, 0.3), rgba(60, 60, 70, 0.3));
    backdrop-filter: blur(5px);
}

/* Hamburger icon styles */
#hamburger-icon {
    display: none; /* Hide by default on desktop */
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    z-index: 10001;
    color: var(--white);
    font-size: 1.5rem;
    padding: 8px;
    background: rgba(40, 40, 50, 0.8);
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

#hamburger-icon:hover {
    background: rgba(50, 50, 60, 0.9);
    border-color: rgba(255, 255, 255, 0.3);
}

/* Mobile close button - COMPLETELY HIDDEN on desktop */
.mobile-close {
    display: none !important;
}

.theme-preview {
    width: 100%;
    height: 100%;
    border: none;
}

footer {
    bottom: 0;
    position: initial;
    width: 100%;
    text-align: center;
}

/* Form Group Styles */
.form-group {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: linear-gradient(135deg, rgba(45, 45, 55, 0.7), rgba(55, 55, 65, 0.7));
    padding: 15px;
    margin-bottom: 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
    backdrop-filter: blur(3px);
}

.form-group:hover {
    background: linear-gradient(135deg, rgba(50, 50, 60, 0.8), rgba(60, 60, 70, 0.8));
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateY(-1px);
}

.form-group label {
    flex-grow: 1; /* Allow label to fill available space */
    margin-right: 10px;
    color: #fff;
    position: relative;
}

.form-group input[type="checkbox"] {
    flex-shrink: 0; /* Prevent checkbox from shrinking */
}

input.form-control:not([type="checkbox"]) {
    width: 60%;
    padding: 10px 15px;
    margin-bottom: 0;
    box-sizing: border-box;
    color: #ffffff;
    background: linear-gradient(135deg, rgba(30, 30, 40, 0.8), rgba(40, 40, 50, 0.8));
    border-radius: 8px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

input.form-control:not([type="checkbox"]):focus {
    outline: none;
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(35, 35, 45, 0.9), rgba(45, 45, 55, 0.9));
    box-shadow: 0 0 10px rgba(var(--primary-color), 0.3);
}

label {
    display: block;
    font-weight: bold;
    color: #fff;
}

input.form-control {
    width: 100%;
    padding: 8px;
    box-sizing: border-box;
    color: #ffffff;
    background-color: var(--gray-900);
    border-radius: var(--br-default);
    border-color: transparent;
}

.form-group input[type="color"] {
    width: 65vw;
    border: none;
    border-radius: inherit; /* Inherit the border-radius from form-group */
    padding: 0;
    cursor: pointer;
}

form {
    height: auto;
    margin-top: 0;
    position: relative;
    width: 100%;
}

#announcement_type {
    padding: 5px;
}

/* Header styling */
.header {
    display: flex;
    align-items: center;
    width: calc(100% - 10%);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--white);
    text-transform: uppercase;
    letter-spacing: 1px;
    justify-content: space-between;
    cursor: pointer;
    padding: 15px 20px;
    background: linear-gradient(135deg, rgba(40, 40, 50, 0.8), rgba(60, 60, 70, 0.8));
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--br-default);
    margin-top: 15px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(5px);
}

.header:hover {
    background: linear-gradient(135deg, rgba(50, 50, 60, 0.9), rgba(70, 70, 80, 0.9));
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

/* Section content hidden by default */
.section-content {
    display: none;
    align-items: center;
    width: auto;
    flex-direction: column;
    padding: 20px;
    background: linear-gradient(135deg, rgba(25, 25, 35, 0.6), rgba(35, 35, 45, 0.6));
    border-radius: var(--br-default);
    margin-bottom: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(3px);
    position: relative;
    z-index: 1;
}

/* Give announcement settings section higher z-index when open */
#announcement-settings {
    z-index: 10000;
}

/* Add spacing when icon dropdown is active to prevent overlap with next form group */
#announcement-settings .icon-selector-container.dropdown-active {
    margin-bottom: 320px; /* Height of dropdown + some extra spacing */
}

#announcement-settings .icon-selector-container .icon-dropdown.active {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
}

/* Toggle icon styling */
.header i {
    transition: transform 0.3s ease;
    padding: 10px;
    border-color: #8080804f;
    border-width: 2px;
    border-style: solid;
    border-radius: var(--br-default);
}

/* Rotate the icon when the section is open */
.header.open i {
    transform: rotate(180deg);
}

button {
    margin-top: 5%;
    width: 97%;
    padding: 10px;
    background-color: transparent;
    color: #fff;
    border: 2px solid #fff;
    cursor: pointer;
}

button:hover {
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

/* Exit button container styling */
#exit-button {
    position: fixed; /* Position fixed for top-left placement */
    top: 15px; /* Spacing from the top */
    left: -2px; /* Spacing from the left */
    z-index: 10000; /* Ensure it's above other elements */
}

/* Exit button styling */
.btn-exit {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.3));
    color: var(--white);
    text-decoration: none;
    font-weight: bold;
    border: 2px solid rgba(239, 68, 68, 0.6);
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.9rem;
    letter-spacing: 0.3px;
}

/* Hover effects */
.btn-exit:hover {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.5), rgba(220, 38, 38, 0.5));
    color: var(--white);
    border-color: rgba(239, 68, 68, 0.9);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    transform: translateY(-1px);
}

.btn-exit i {
    margin-right: 6px;
}


p {
    text-align: center;
    color: #ffffff;
}

#announcement_type.form-control {
    background-color: var(--gray-900);
    padding: 2%;
    border-color: transparent;
    border-radius: 15px;
    color: white;
    padding-right: 1%;
}

/* Dropdown option styling */
select option {
    background: rgba(30, 30, 40, 0.9) !important;
    color: #fff !important;
}

select option:hover {
    background: rgba(50, 50, 60, 0.9) !important;
    color: #fff !important;
}

select option:checked {
    background: rgba(59, 130, 246, 0.3) !important;
    color: #fff !important;
}

/* Custom Tooltip Styling */
.form-group label[data-tooltip] {
    position: relative;
    cursor: pointer;
}

/* Tooltip Styles */
.form-group label[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 100;
    top: 20;
    transform: translate(-50%, -100%);
    background-color: rgba(0, 0, 0, 0.7);
    color: #ffffff;
    padding: 5px 10px;
    border-radius: 5px;
    white-space: nowrap;
    font-size: 12px;
    z-index: 1000; /* Ensure tooltip is above other elements */
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    pointer-events: none;
}

.form-group label[data-tooltip]:hover::after {
    opacity: 1;
}

/* Responsive Tooltip Adjustments */
@media (max-width: 600px) {
    .form-group label[data-tooltip]::after {
        left: 0;
        transform: translate(0, -100%);
    }
}

@media (max-width: 930px) {
    /* Mobile Layout - Stack vertically */
    .main-container {
        display: grid;
        grid-template-areas: 
            "exit"
            "settings"
            "preview";
        grid-template-rows: 60px auto 1fr;
        grid-template-columns: 1fr;
        height: 100vh;
    }

    /* Mobile Exit Area */
    .exit-area {
        padding: 8px 15px;
        justify-content: space-between;
    }

    /* Mobile hamburger icon - Show on mobile */
    #hamburger-icon {
        display: block;
    }

    /* Hide settings by default on mobile */
    .settings-area {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10000;
        background: linear-gradient(135deg, rgba(15, 15, 25, 0.98), rgba(25, 25, 35, 0.98));
        backdrop-filter: blur(15px);
        overflow-y: auto;
    }

    /* Show settings when toggled */
    .settings-area.mobile-show {
        display: flex;
    }

    #settings-container {
        width: 100%;
        height: 100%;
        padding: 0;
    }

    .form-container {
        padding: 10px;
        height: calc(100vh - 140px);
        overflow-y: scroll;
        overflow-x: hidden;
        width: calc(100% - 10%);
    }

    .save-container {
        padding: 15px;
        position: sticky;
        bottom: 0;
        background: linear-gradient(135deg, rgba(20, 20, 30, 0.95), rgba(30, 30, 40, 0.95));
        backdrop-filter: blur(10px);
        border-top: 2px solid rgba(255, 255, 255, 0.1);
    }

    /* Mobile Exit Button */
    .btn-exit {
        padding: 8px 12px;
        font-size: 0.8rem;
        border-radius: 6px;
    }

    /* Add close button for mobile settings - Only exists on mobile */
    .mobile-close {
        display: none;
        position: relative;
        top: auto;
        right: auto;
        background: rgba(239, 68, 68, 0.3);
        border: 2px solid rgba(239, 68, 68, 0.6);
        color: var(--white);
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        z-index: 10001;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: calc(100% - 20px);
        margin: 10px;
        text-align: center;
        font-weight: bold;
    }

    .mobile-close:hover {
        background: rgba(239, 68, 68, 0.5);
        border-color: rgba(239, 68, 68, 0.9);
    }

    /* Show close button when settings are open on mobile */
    .settings-area.mobile-show .mobile-close {
        display: block !important;
    }

    /* Mobile Headers */
    .header {
        width: calc(100% - 20px);
        margin: 10px;
        padding: 12px 15px;
        font-size: 1rem;
        background: linear-gradient(135deg, rgba(40, 40, 50, 0.9), rgba(60, 60, 70, 0.9));
    }

    /* Mobile Section Content */
    .section-content {
        width: calc(100% - 20px);
        margin: 0 10px 15px 10px;
        padding: 15px;
        background: linear-gradient(135deg, rgba(25, 25, 35, 0.8), rgba(35, 35, 45, 0.8));
    }

    /* Mobile Form Groups */
    .form-group {
        margin: 0 0 12px 0;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        background: linear-gradient(135deg, rgba(45, 45, 55, 0.8), rgba(55, 55, 65, 0.8));
        padding: 10px;
        width: calc(100% - 10%);
    }

    .form-group label {
        margin-right: 0;
        margin-bottom: 5px;
        width: 100%;
    }

    .form-group input:not([type="checkbox"]) {
        width: 100%;
    }

    .form-group input[type="checkbox"] {
        align-self: flex-end;
        margin-top: -30px;
    }

    /* Mobile Preview */
    .preview-area {
        display: none;
    }

    .preview-area.mobile-show {
        display: flex;
        height: calc(100vh - 60px);
    }

    #live-preview {
        margin: 10px;
        height: calc(100% - 20px);
    }

    /* Mobile Save Button */
    .btn-primary {
        width: calc(100% - 20px);
        margin: 10px;
        padding: 15px;
        font-size: 1.1rem;
    }

    /* Hide desktop description */
    .form-container p {
        display: none;
    }
}

/* Icon Selector Styles */
.icon-selector-container {
    position: relative;
    width: 60%;
}

.icon-selector {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: auto;
    padding: 10px 15px;
    background: linear-gradient(135deg, rgba(30, 30, 40, 0.8), rgba(40, 40, 50, 0.8));
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #fff;
}

.icon-selector:hover {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(35, 35, 45, 0.9), rgba(45, 45, 55, 0.9));
    box-shadow: 0 0 10px rgba(var(--primary-color), 0.3);
}

.selected-icon {
    display: flex;
    align-items: center;
    gap: 8px;
}

.selected-icon i {
    font-size: 1.2em;
    color: var(--primary-color);
    min-width: 20px;
}

.icon-name {
    font-size: 0.9em;
    color: #ccc;
}

.dropdown-arrow {
    transition: transform 0.3s ease;
    color: #999;
}

.icon-selector.active .dropdown-arrow {
    transform: rotate(180deg);
}

.icon-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, rgba(20, 20, 30, 0.95), rgba(30, 30, 40, 0.95));
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    z-index: 9999;
    max-height: 300px;
    overflow: hidden;
    display: none;
    margin-top: 5px;
}

.icon-dropdown.active {
    display: block;
}

.icon-search {
    position: relative;
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.icon-search input {
    width: 100% !important;
    padding: 10px 40px 10px 15px !important;
    background: rgba(40, 40, 50, 0.8) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 6px !important;
    color: #fff !important;
    font-size: 0.9em !important;
    margin: 0 !important;
}

.icon-search input:focus {
    outline: none !important;
    border-color: var(--primary-color) !important;
    background: rgba(45, 45, 55, 0.9) !important;
}

.search-icon {
    position: absolute;
    right: 25px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

.icon-list {
    max-height: 200px;
    overflow-y: auto;
    padding: 10px;
}

.icon-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s ease;
    color: #ccc;
}

.icon-item:hover {
    background: rgba(59, 130, 246, 0.2);
    color: #fff;
}

.icon-item i {
    font-size: 1.1em;
    color: var(--primary-color);
    min-width: 20px;
    text-align: center;
}

.icon-item .icon-class {
    font-size: 0.85em;
    font-family: 'Courier New', monospace;
}

/* Scrollbar for icon list */
.icon-list::-webkit-scrollbar {
    width: 6px;
}

.icon-list::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.icon-list::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.icon-list::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
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
    <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/icon.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/extensions/euphoriatheme/icon.png">
    <link rel="icon" type="image/png" href="/assets/extensions/euphoriatheme/icon.png" sizes="16x16">
    <link rel="shortcut icon" href="/assets/extensions/euphoriatheme/icon.png">
@endif
    <link rel="manifest" href="/favicons/manifest.json">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#0e4688">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@show

<style>
    @import url('//fonts.googleapis.com/css?family=Rubik:300,400,500&display=swap');
    @import url('//fonts.googleapis.com/css?family=IBM+Plex+Mono|IBM+Plex+Sans:500&display=swap');
</style>
</head>

<div class="main-container">
    <!-- Mobile Hamburger Icon -->
    <div id="hamburger-icon" onclick="toggleMobileSettings()">
        <i class="fa-solid fa-bars"></i>
    </div>

    <!-- Exit Button Area -->
    <div class="exit-area">
        <button class="btn-exit" onclick="exitCustomiser()">
            <i class="fa-solid fa-door-open"></i>
            Exit Customizer
        </button>
    </div>

    <!-- Settings Area -->
    <div class="settings-area" id="settings-panel">
        <div id="settings-container">
            <div class="form-container">
                <form id="theme-settings-form" method="POST" action="{{ route('blueprint.extensions.euphoriatheme.wrapper.admin.themeCustomiser.save') }}">
                {{ csrf_field() }}
        <p>Hover over Icons to see Description</p>
        
        <!-- General Settings Toggle -->
        <div class="header" onclick="toggleSection('general-settings')">
            General
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="general-settings" class="section-content">
            <div class="form-group">
                <label data-tooltip="Primary color of your theme"><i class="fa-solid fa-palette"></i></label>
                <input type="color" name="primary_color" id="primary_color" value="{{ $primary_color }}">
            </div>
            <div class="form-group">
                <label data-tooltip="URL of your logo image"><i class="fa-regular fa-image"></i></label>
                <input type="text" name="logo_url" id="logo_url" value="{{ $logo_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Footer text (HTML supported)"><i class="fa-solid fa-shoe-prints"></i></label>
                <input type="text" name="footer_text" id="footer_text" value="{{ $footer_text }}">
            </div>
            <div class="form-group">
                <label data-tooltip="URL of your favicon"><i class="fa-regular fa-image"></i></label>
                <input type="text" name="favicon_url" id="favicon_url" value="{{ $favicon_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Enter the URL of your main background image"><i class="fa-regular fa-image"></i></label>
                <input type="text" name="background_url" id="background_url" value="{{ $background_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Profile Picture URLs"><i class="fa-solid fa-user"></i> Profile Pictures</label>
                <input type="hidden" name="profile_picture_url_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="profile-picture-url-toggle" name="profile_picture_url_enabled" value="1" {{ $profile_picture_url_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Enable SideNav"><i class="fa-solid fa-palette"></i> Side Navigation</label>
                <input type="hidden" name="side_nav_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="side-nav-toggle" name="side_nav_enabled" value="1" {{ $side_nav_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Company Info When using SideNav"><i class="fa-solid fa-palette"></i> Company Info Side Navigation</label>
                <input type="hidden" name="side_nav_company_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="side-nav-company-toggle" name="side_nav_company_enabled" value="1" {{ $side_nav_company_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Cookie Popup"><i class="fa-solid fa-cookie-bite"></i> Cookie Banner</label>
                <input type="hidden" name="cookie_alert_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="cookie-alert-toggle" name="cookie_alert_enabled" value="1" {{ $cookie_alert_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Site Name in Top NavBar"><i class="fa-solid fa-heading"></i> Top Nav Site Name</label>
                <input type="hidden" name="site_name_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="site-name-toggle" name="site_name_enabled" value="1" {{ $site_name_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Server Status Tooltips"><i class="fa-solid fa-lightbulb"></i> Server Status Badges</label>
                <input type="hidden" name="server_tooltips_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="server-tooltips-toggle" name="server_tooltips_enabled" value="1" {{ $server_tooltips_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Put site into maintenance mode for non-admins"><i class="fa-solid fa-shield-halved"></i> Maintenance Mode</label>
                <input type="hidden" name="maintenance_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="maintenance-toggle" name="maintenance_enabled" value="1" {{ isset($maintenance_enabled) && $maintenance_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Message to show on the maintenance page"><i class="fa-solid fa-comment-dots"></i> Maintenance Message</label>
                <input type="text" name="maintenance_message" id="maintenance_message" value="{{ $maintenance_message ?? '' }}" class="form-control">
            </div>
            <div class="form-group">
                <label data-tooltip="Background image URL for the maintenance page"><i class="fa-solid fa-image"></i> Maintenance Background URL</label>
                <input type="text" name="maintenance_background_url" id="maintenance_background_url" value="{{ $maintenance_background_url ?? '' }}" class="form-control">
            </div>
        </div>

        <!-- Background Settings Toggle -->
        <div class="header" onclick="toggleSection('auth-settings')">
            Authentication Page
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="auth-settings" class="section-content">
            <div class="form-group">
                <label data-tooltip="Enter the URL of your login background image"><i class="fa-regular fa-image"></i></label>
                <input type="text" name="login_background_url" id="login_background_url" value="{{ $login_background_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Enable Logo on Login Page"><i class="fa-regular fa-image"></i> Show Logo on Login Page</label>
                <input type="hidden" name="show-logo-login-toggle" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="show-logo-login-toggle" name="show_logo_login_enabled" value="1" {{ $show_logo_login_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Show Euphoria Theme Advert"><i class="fa-regular fa-image"></i> Show Theme Advert</label>
                <input type="hidden" name="advert_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="advert-toggle" name="advert_enabled" value="1" {{ $advert_enabled ? 'checked' : '' }}>
            </div>
        </div>

        <!-- Background Settings Toggle -->
        <div class="header" onclick="toggleSection('background-settings')">
            Server Backgrounds
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="background-settings" class="section-content">
            <div class="form-group">
                <label data-tooltip="Enable Server background images"><i class="fa-regular fa-image"></i> Server Backgrounds</label>
                <input type="hidden" name="background_image_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="background-image-toggle" name="background_image_enabled" value="1" {{ $background_image_enabled ? 'checked' : '' }}>
            </div>
        </div>

        <!-- Loading Screen Settings -->
        <div class="header" onclick="toggleSection('loading-screen-settings')">
            Loading Screen
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="loading-screen-settings" class="section-content">
        <div class="form-group">
            <label data-tooltip="Enable Loading Screen"><i class="fa-solid fa-spinner"></i> Loading Screen</label>
            <input type="hidden" name="loading_screen_enabled" value="0"> <!-- Hidden input for unchecked state -->
            <input type="checkbox" id="loading-screen-toggle" name="loading_screen_enabled" value="1" {{ $loading_screen_enabled ? 'checked' : '' }}>
        </div>
        <div class="form-group">
            <label data-tooltip="URL of your loading screen background image"><i class="fa-regular fa-image"></i></label>
            <input type="text" name="loading_screen_background_url" id="loading_screen_background_url" value="{{ $loading_screen_background_url }}">
        </div>
        <div class="form-group">
            <label data-tooltip="URL of your loading screen logo"><i class="fa-regular fa-image"></i></label>
            <input type="text" name="loading_screen_logo_url" id="loading_screen_logo_url" value="{{ $loading_screen_logo_url }}">
        </div>
        <div class="form-group">
            <label for="loading_screen_mode" data-tooltip="Choose how the loading screen should behave">Loading Mode</label>
            <select id="loading_screen_mode" name="loading_screen_mode" class="form-control" style="width: 60%; padding: 10px 15px; background: linear-gradient(135deg, rgba(30, 30, 40, 0.8), rgba(40, 40, 50, 0.8)); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 8px; color: #fff;">
                <option value="timer" {{ ($loading_screen_mode ?? 'timer') === 'timer' ? 'selected' : '' }} style="background: rgba(30, 30, 40, 0.9); color: #fff;">Timer-based</option>
                <option value="page_load" {{ ($loading_screen_mode ?? 'timer') === 'page_load' ? 'selected' : '' }} style="background: rgba(30, 30, 40, 0.9); color: #fff;">Until page loads</option>
            </select>
        </div>
        <div class="form-group" id="timer-duration-group">
            <label for="loading_screen_duration" data-tooltip="Duration in seconds for timer-based loading screen">Duration (seconds)</label>
            <input type="number" id="loading_screen_duration" name="loading_screen_duration" class="form-control" value="{{ $loading_screen_duration ?? 4 }}" min="1" max="30" placeholder="4">
        </div>
        </div>

        <!-- Seasonal Settings -->
        <div class="header" onclick="toggleSection('seasonal-settings')">
            Seasonal Effects
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="seasonal-settings" class="section-content">
        <div class="form-group">
                <label data-tooltip="Christmas Spirit"><i class="fa-solid fa-snowflake"></i> Let it Snow</label>
                <input type="hidden" name="snowing_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="snowing-enabled-toggle" name="snowing_enabled" value="1" {{ $snowing_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Halloween Spirit"><i class="fa-solid fa-ghost"></i> Spooky Season</label>
                <input type="hidden" name="halloween_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="halloween-enabled-toggle" name="halloween_enabled" value="1" {{ $halloween_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label data-tooltip="Easter Spirit"><i class="fa-solid fa-egg"></i> Easter</label>
                <input type="hidden" name="easter_enabled" value="0"> <!-- Hidden input for unchecked state -->
                <input type="checkbox" id="easter-enabled-toggle" name="easter_enabled" value="1" {{ $easter_enabled ? 'checked' : '' }}>
            </div>
        </div>
        
        <!-- Announcement Settings -->
        <div class="header" onclick="toggleSection('announcement-settings')">
            Announcements
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="announcement-settings" class="section-content">
        <div class="form-group" data-tooltip="Style of Your Announcement">
            <label for="announcement_type">Type:</label>
            <select name="announcement_type" id="announcement_type" required>
                <option value="standard" {{ $announcement_type === 'standard' ? 'selected' : '' }}>Standard</option>
                <option value="warning" {{ $announcement_type === 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="alert" {{ $announcement_type === 'alert' ? 'selected' : '' }}>Alert</option>
                <option value="success" {{ $announcement_type === 'success' ? 'selected' : '' }}>Success</option>
                <option value="disable" {{ $announcement_type === 'disable' ? 'selected' : '' }}>Disable</option>
            </select>
        </div>
        <!-- Icon Field -->
        <div class="form-group" data-tooltip="Icon for Announcement">
            <label for="announcement_icon">Icon:</label>
            <div class="icon-selector-container">
                <div class="icon-selector" onclick="toggleIconDropdown()">
                    <span class="selected-icon">
                        <i class="{{ $announcement_icon ?: 'fa-solid fa-info-circle' }}"></i>
                        <span class="icon-name">{{ $announcement_icon ?: 'fa-info-circle' }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
                </div>
                <div class="icon-dropdown" id="iconDropdown">
                    <div class="icon-search">
                        <input type="text" placeholder="Search icons..." id="iconSearch" oninput="filterIcons(this.value)">
                        <i class="fa-solid fa-search search-icon"></i>
                    </div>
                    <div class="icon-list" id="iconList">
                        <!-- Icons will be populated by JavaScript -->
                    </div>
                </div>
                <input type="hidden" name="announcement_icon" id="announcement_icon" value="{{ $announcement_icon }}">
            </div>
        </div>

        <!-- Text Content Field -->
        <div class="form-group" data-tooltip="Text for Announcement">
            <label for="announcement_content">Text:</label>
            <input type="text" name="announcement_content"  value="{{ $announcement_content }}" id="announcement_content" rows="4" placeholder="Enter the announcement message">
        </div>
        </div>

        <!-- TX Admin Settings Toggle -->
        <div class="header" onclick="toggleSection('txadmin-settings')">
            TX Admin Integration
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div id="txadmin-settings" class="section-content">
            <div class="form-group">
                <label for="tx_admin_enabled" data-tooltip="Enable TX Admin integration in the side navigation">Enable TX Admin</label>
                <input type="checkbox" id="tx_admin_enabled" name="tx_admin_enabled" value="1" {{ $tx_admin_enabled ? 'checked' : '' }}>
            </div>
            <div class="form-group">
                <label for="tx_admin_egg_id" data-tooltip="The egg ID that should show the TX Admin button (e.g., FiveM server egg ID)">TX Admin Egg ID</label>
                <input type="text" id="tx_admin_egg_id" name="tx_admin_egg_id" class="form-control" value="{{ $tx_admin_egg_id ?? '' }}" placeholder="Enter egg ID (e.g., 5)">
            </div>
        </div>

        <!-- Language API Settings Toggle -->
        <div class="header" onclick="toggleSection('language-api-settings')">
            Language API Settings
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div id="language-api-settings" class="section-content">
            <div class="form-group">
                <label for="language_api_url" data-tooltip="Custom language API URL for self-hosting (leave empty to use default)">Language API URL</label>
                <input type="text" id="language_api_url" name="language_api_url" class="form-control" value="{{ $language_api_url ?? '' }}" placeholder="https://api.yourdomain.com">
            </div>
        </div>

        <!-- Buttons Settings Toggle -->
        <div class="header" onclick="toggleSection('button-settings')">
            Custom Buttons
            <i class="fa-solid fa-chevron-down"></i> <!-- Icon that rotates -->
        </div>
        <div id="button-settings" class="section-content">
            <div class="form-group">
                <label data-tooltip="Enter the URL of your Discord server"><i class="fa-brands fa-discord"></i></label>
                <input type="text" name="discord_url" id="discord_url" value="{{ $discord_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Enter the URL of your store"><i class="fa-solid fa-store"></i></label>
                <input type="text" name="store_url" id="store_url" value="{{ $store_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Enter the URL of your status page"><i class="fa-solid fa-signal"></i></label>
                <input type="text" name="status_url" id="status_url" value="{{ $status_url }}">
            </div>
            <div class="form-group">
                <label data-tooltip="Show custom buttons in the Side Navigation"><i class="fa-solid fa-list"></i> Show buttons in SideNav</label>
                <input type="hidden" name="custom_buttons_in_side_nav" value="0">
                <input type="checkbox" id="cb-placement-sidenav" name="custom_buttons_in_side_nav" value="1" {{ ($custom_buttons_in_side_nav ?? true) ? 'checked' : '' }}>
            </div>

            <div class="form-group" id="custom-buttons-editor-container">
                <label data-tooltip="Create custom navigation buttons">Custom Navigation Buttons</label>
                <div style="width:100%;">
                    <button type="button" id="add-custom-button" class="btn btn-default" style="margin-bottom:12px;">Add Button</button>
                    <div id="custom-buttons-list" style="display:flex;flex-direction:column;gap:8px;"></div>
                    <input type="hidden" id="custom_buttons" name="custom_buttons" value='@json($custom_buttons ?? [])'>
                </div>
            </div>
        </div>

                </form>
            </div>

            <!-- Mobile Close Button (only styled on mobile) -->
            <button class="mobile-close" onclick="toggleMobileSettings()">
                <i class="fa-solid fa-times"></i>
                Close Settings
            </button>

            <!-- Save Button Container -->
            <div class="save-container">
                <button type="submit" form="theme-settings-form" class="btn btn-primary" title="Save your theme settings">
                    <i class="fa-solid fa-floppy-disk"></i> Save Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Area -->
    <div class="preview-area">
        <div id="live-preview">
            <iframe id="preview-frame" src="/" frameborder="0" class="theme-preview"></iframe>
        </div>
    </div>
</div>

<script>
    function exitCustomiser() {
        // Redirect to the admin page
        window.location.href = '/admin';
    }

    // Mobile settings toggle
    function toggleMobileSettings() {
        const settingsArea = document.getElementById('settings-panel');
        const previewArea = document.querySelector('.preview-area');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        
        console.log('Toggle called, window width:', window.innerWidth);
        
        if (window.innerWidth <= 930) {
            const isSettingsOpen = settingsArea.classList.contains('mobile-show');
            console.log('Settings currently open:', isSettingsOpen);
            
            if (isSettingsOpen) {
                // Close settings, show preview
                settingsArea.classList.remove('mobile-show');
                previewArea.classList.add('mobile-show');
                if (hamburgerIcon) {
                    hamburgerIcon.style.display = 'block';
                    hamburgerIcon.style.visibility = 'visible';
                }
                console.log('Closing settings, showing preview');
            } else {
                // Open settings, hide preview
                settingsArea.classList.add('mobile-show');
                previewArea.classList.remove('mobile-show');
                if (hamburgerIcon) {
                    hamburgerIcon.style.display = 'none';
                    hamburgerIcon.style.visibility = 'hidden';
                }
                console.log('Opening settings, hiding preview');
            }
        }
    }

    // Legacy function for backward compatibility
    function toggleSettings() {
        toggleMobileSettings();
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        const settingsArea = document.getElementById('settings-panel');
        const previewArea = document.querySelector('.preview-area');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        
        console.log('Resize event, window width:', window.innerWidth);
        
        if (window.innerWidth > 930) {
            // Desktop mode - remove mobile classes and hide hamburger
            settingsArea.classList.remove('mobile-show');
            previewArea.classList.remove('mobile-show');
            if (hamburgerIcon) {
                hamburgerIcon.style.display = 'none';
                hamburgerIcon.style.visibility = 'hidden';
            }
            console.log('Desktop mode activated');
        } else {
            // Mobile mode - show preview by default if settings aren't open
            if (!settingsArea.classList.contains('mobile-show')) {
                previewArea.classList.add('mobile-show');
                if (hamburgerIcon) {
                    hamburgerIcon.style.display = 'block';
                    hamburgerIcon.style.visibility = 'visible';
                }
                console.log('Mobile mode: showing preview');
            } else {
                previewArea.classList.remove('mobile-show');
                if (hamburgerIcon) {
                    hamburgerIcon.style.display = 'none';
                    hamburgerIcon.style.visibility = 'hidden';
                }
                console.log('Mobile mode: settings open');
            }
        }
    });

    // Toggle section content visibility and rotate icon
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const header = section.previousElementSibling;

        // Get the current open sections from localStorage or initialize as an empty object
        let openSections = JSON.parse(localStorage.getItem('openSections')) || {};

        if (section.style.display === "none" || section.style.display === "") {
            section.style.display = "flex"; // Show section content
            header.classList.add('open'); // Rotate the icon

            // Save the section as open in localStorage
            openSections[sectionId] = true;
        } else {
            section.style.display = "none"; // Hide section content
            header.classList.remove('open'); // Reset the icon rotation

            // Save the section as closed in localStorage
            openSections[sectionId] = false;
        }

        // Save the updated openSections state in localStorage
        localStorage.setItem('openSections', JSON.stringify(openSections));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const previewFrame = document.getElementById('preview-frame');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        
        console.log('DOM loaded, window width:', window.innerWidth);
        
        // Make sure the iframe is loaded before accessing its content
        previewFrame.onload = function () {
            const iframeDocument = previewFrame.contentWindow.document;
            const LogoutElement = iframeDocument.querySelector('button#NavigationLogout.navigation-button');

            if (LogoutElement) {
                LogoutElement.style.display = 'none'; // Hide the logout element in the iframe preview
            }
        };

        // Restore the open/closed state of the sections
        const openSections = JSON.parse(localStorage.getItem('openSections')) || {};
        for (const sectionId in openSections) {
            if (openSections[sectionId]) {
                const section = document.getElementById(sectionId);
                if (section) {
                    const header = section.previousElementSibling;
                    section.style.display = "flex"; // Show the section
                    header.classList.add('open'); // Rotate the icon
                }
            }
        }

        // Initialize mobile state
        if (window.innerWidth <= 930) {
            document.querySelector('.preview-area').classList.add('mobile-show');
            if (hamburgerIcon) {
                hamburgerIcon.style.display = 'block';
                hamburgerIcon.style.visibility = 'visible';
            }
            console.log('Mobile mode initialized');
        } else {
            if (hamburgerIcon) {
                hamburgerIcon.style.display = 'none';
                hamburgerIcon.style.visibility = 'hidden';
            }
            console.log('Desktop mode initialized');
        }
    });

    // Font Awesome icon list - must be defined before custom buttons editor
    const fontAwesomeIcons = [
        { class: 'fa-info-circle', prefix: 'fa-solid' },
        { class: 'fa-exclamation-triangle', prefix: 'fa-solid' },
        { class: 'fa-check-circle', prefix: 'fa-solid' },
        { class: 'fa-times-circle', prefix: 'fa-solid' },
        { class: 'fa-bell', prefix: 'fa-solid' },
        { class: 'fa-envelope', prefix: 'fa-solid' },
        { class: 'fa-heart', prefix: 'fa-solid' },
        { class: 'fa-star', prefix: 'fa-solid' },
        { class: 'fa-user', prefix: 'fa-solid' },
        { class: 'fa-home', prefix: 'fa-solid' },
        { class: 'fa-cog', prefix: 'fa-solid' },
        { class: 'fa-search', prefix: 'fa-solid' },
        { class: 'fa-plus', prefix: 'fa-solid' },
        { class: 'fa-minus', prefix: 'fa-solid' },
        { class: 'fa-edit', prefix: 'fa-solid' },
        { class: 'fa-trash', prefix: 'fa-solid' },
        { class: 'fa-download', prefix: 'fa-solid' },
        { class: 'fa-upload', prefix: 'fa-solid' },
        { class: 'fa-print', prefix: 'fa-solid' },
        { class: 'fa-floppy-disk', prefix: 'fa-solid' },
        { class: 'fa-share', prefix: 'fa-solid' },
        { class: 'fa-link', prefix: 'fa-solid' },
        { class: 'fa-calendar', prefix: 'fa-solid' },
        { class: 'fa-clock', prefix: 'fa-solid' },
        { class: 'fa-location-dot', prefix: 'fa-solid' },
        { class: 'fa-phone', prefix: 'fa-solid' },
        { class: 'fa-at', prefix: 'fa-solid' },
        { class: 'fa-globe', prefix: 'fa-solid' },
        { class: 'fa-wifi', prefix: 'fa-solid' },
        { class: 'fa-signal', prefix: 'fa-solid' },
        { class: 'fa-battery-full', prefix: 'fa-solid' },
        { class: 'fa-volume-high', prefix: 'fa-solid' },
        { class: 'fa-play', prefix: 'fa-solid' },
        { class: 'fa-pause', prefix: 'fa-solid' },
        { class: 'fa-stop', prefix: 'fa-solid' },
        { class: 'fa-forward', prefix: 'fa-solid' },
        { class: 'fa-backward', prefix: 'fa-solid' },
        { class: 'fa-forward-step', prefix: 'fa-solid' },
        { class: 'fa-backward-step', prefix: 'fa-solid' },
        { class: 'fa-eject', prefix: 'fa-solid' },
        { class: 'fa-chevron-left', prefix: 'fa-solid' },
        { class: 'fa-chevron-right', prefix: 'fa-solid' },
        { class: 'fa-chevron-up', prefix: 'fa-solid' },
        { class: 'fa-chevron-down', prefix: 'fa-solid' },
        { class: 'fa-arrow-left', prefix: 'fa-solid' },
        { class: 'fa-arrow-right', prefix: 'fa-solid' },
        { class: 'fa-arrow-up', prefix: 'fa-solid' },
        { class: 'fa-arrow-down', prefix: 'fa-solid' },
        { class: 'fa-magnifying-glass-plus', prefix: 'fa-solid' },
        { class: 'fa-magnifying-glass-minus', prefix: 'fa-solid' },
        { class: 'fa-expand', prefix: 'fa-solid' },
        { class: 'fa-compress', prefix: 'fa-solid' },
        { class: 'fa-eye', prefix: 'fa-solid' },
        { class: 'fa-eye-slash', prefix: 'fa-solid' },
        { class: 'fa-lock', prefix: 'fa-solid' },
        { class: 'fa-unlock', prefix: 'fa-solid' },
        { class: 'fa-shield', prefix: 'fa-solid' },
        { class: 'fa-fire', prefix: 'fa-solid' },
        { class: 'fa-wand-magic-sparkles', prefix: 'fa-solid' },
        { class: 'fa-truck', prefix: 'fa-solid' },
        { class: 'fa-dollar-sign', prefix: 'fa-solid' },
        { class: 'fa-building-columns', prefix: 'fa-solid' },
        { class: 'fa-graduation-cap', prefix: 'fa-solid' },
        { class: 'fa-kit-medical', prefix: 'fa-solid' },
        { class: 'fa-stethoscope', prefix: 'fa-solid' },
        { class: 'fa-heartbeat', prefix: 'fa-solid' },
        { class: 'fa-truck-medical', prefix: 'fa-solid' },
        { class: 'fa-square-h', prefix: 'fa-solid' },
        { class: 'fa-square-plus', prefix: 'fa-solid' },
        { class: 'fa-wheelchair', prefix: 'fa-solid' },
        { class: 'fa-car', prefix: 'fa-solid' },
        { class: 'fa-taxi', prefix: 'fa-solid' },
        { class: 'fa-bus', prefix: 'fa-solid' },
        { class: 'fa-bicycle', prefix: 'fa-solid' },
        { class: 'fa-motorcycle', prefix: 'fa-solid' },
        { class: 'fa-ship', prefix: 'fa-solid' },
        { class: 'fa-plane', prefix: 'fa-solid' },
        { class: 'fa-rocket', prefix: 'fa-solid' },
        { class: 'fa-anchor', prefix: 'fa-solid' },
        { class: 'fa-umbrella', prefix: 'fa-solid' },
        { class: 'fa-lightbulb', prefix: 'fa-solid' },
        { class: 'fa-gift', prefix: 'fa-solid' },
        { class: 'fa-film', prefix: 'fa-solid' },
        { class: 'fa-camera', prefix: 'fa-solid' },
        { class: 'fa-music', prefix: 'fa-solid' },
        { class: 'fa-headphones', prefix: 'fa-solid' },
        { class: 'fa-microphone', prefix: 'fa-solid' },
        { class: 'fa-video', prefix: 'fa-solid' },
        { class: 'fa-tv', prefix: 'fa-solid' },
        { class: 'fa-desktop', prefix: 'fa-solid' },
        { class: 'fa-laptop', prefix: 'fa-solid' },
        { class: 'fa-tablet', prefix: 'fa-solid' },
        { class: 'fa-mobile', prefix: 'fa-solid' },
        { class: 'fa-gamepad', prefix: 'fa-solid' },
        { class: 'fa-keyboard', prefix: 'fa-solid' },
        { class: 'fa-mouse-pointer', prefix: 'fa-solid' },
        { class: 'fa-rss', prefix: 'fa-solid' },
        { class: 'fa-podcast', prefix: 'fa-solid' },
        { class: 'fa-newspaper', prefix: 'fa-solid' },
        { class: 'fa-book', prefix: 'fa-solid' },
        { class: 'fa-bookmark', prefix: 'fa-solid' },
        { class: 'fa-building', prefix: 'fa-solid' },
        { class: 'fa-file', prefix: 'fa-solid' },
        { class: 'fa-folder', prefix: 'fa-solid' },
        { class: 'fa-box-archive', prefix: 'fa-solid' },
        { class: 'fa-tags', prefix: 'fa-solid' },
        { class: 'fa-barcode', prefix: 'fa-solid' },
        { class: 'fa-qrcode', prefix: 'fa-solid' },
        { class: 'fa-cart-shopping', prefix: 'fa-solid' },
        { class: 'fa-credit-card', prefix: 'fa-solid' },
        { class: 'fa-trophy', prefix: 'fa-solid' },
        { class: 'fa-certificate', prefix: 'fa-solid' },
        { class: 'fa-thumbs-up', prefix: 'fa-solid' },
        { class: 'fa-thumbs-down', prefix: 'fa-solid' },
        { class: 'fa-comment', prefix: 'fa-solid' },
        { class: 'fa-comments', prefix: 'fa-solid' },
        { class: 'fa-quote-left', prefix: 'fa-solid' },
        { class: 'fa-quote-right', prefix: 'fa-solid' },
        { class: 'fa-facebook', prefix: 'fa-brands' },
        { class: 'fa-twitter', prefix: 'fa-brands' },
        { class: 'fa-instagram', prefix: 'fa-brands' },
        { class: 'fa-linkedin', prefix: 'fa-brands' },
        { class: 'fa-youtube', prefix: 'fa-brands' },
        { class: 'fa-pinterest', prefix: 'fa-brands' },
        { class: 'fa-snapchat', prefix: 'fa-brands' },
        { class: 'fa-tiktok', prefix: 'fa-brands' },
        { class: 'fa-discord', prefix: 'fa-brands' },
        { class: 'fa-twitch', prefix: 'fa-brands' },
        { class: 'fa-reddit', prefix: 'fa-brands' },
        { class: 'fa-github', prefix: 'fa-brands' },
        { class: 'fa-gitlab', prefix: 'fa-brands' },
        { class: 'fa-bitbucket', prefix: 'fa-brands' },
        { class: 'fa-stack-overflow', prefix: 'fa-brands' },
        { class: 'fa-codepen', prefix: 'fa-brands' },
        { class: 'fa-jsfiddle', prefix: 'fa-brands' },
        { class: 'fa-css3', prefix: 'fa-brands' },
        { class: 'fa-html5', prefix: 'fa-brands' },
        { class: 'fa-js', prefix: 'fa-brands' },
        { class: 'fa-react', prefix: 'fa-brands' },
        { class: 'fa-angular', prefix: 'fa-brands' },
        { class: 'fa-vuejs', prefix: 'fa-brands' },
        { class: 'fa-node-js', prefix: 'fa-brands' },
        { class: 'fa-python', prefix: 'fa-brands' },
        { class: 'fa-java', prefix: 'fa-brands' },
        { class: 'fa-android', prefix: 'fa-brands' },
        { class: 'fa-apple', prefix: 'fa-brands' },
        { class: 'fa-windows', prefix: 'fa-brands' },
        { class: 'fa-linux', prefix: 'fa-brands' },
        { class: 'fa-ubuntu', prefix: 'fa-brands' },
        { class: 'fa-chrome', prefix: 'fa-brands' },
        { class: 'fa-firefox', prefix: 'fa-brands' },
        { class: 'fa-safari', prefix: 'fa-brands' },
        { class: 'fa-edge', prefix: 'fa-brands' },
        { class: 'fa-opera', prefix: 'fa-brands' }
    ];

        // --- Custom Buttons Editor ---
        (function() {
            const customButtonsInput = document.getElementById('custom_buttons');
            const customButtonsList = document.getElementById('custom-buttons-list');
            const addCustomButtonBtn = document.getElementById('add-custom-button');

            let buttons = [];
            try {
                let rawValue = customButtonsInput.value || '[]';
                console.log('Custom buttons raw value:', rawValue);
                console.log('Raw value first 50 chars:', rawValue.substring(0, 50));
                
                // Try to parse - might need double-parsing if double-encoded
                let parsed = JSON.parse(rawValue);
                
                // Check if we got a string back (double-encoded)
                if (typeof parsed === 'string') {
                    console.warn('Got string from first parse, trying again (double-encoded data)');
                    parsed = JSON.parse(parsed);
                }
                
                console.log('Parsed custom buttons:', parsed);
                console.log('Is array?', Array.isArray(parsed));
                
                buttons = Array.isArray(parsed) ? parsed : [];
                console.log('Final buttons array:', buttons);
                
                // If we had to double-parse, fix the input value
                if (typeof JSON.parse(rawValue) === 'string') {
                    console.log('Fixing double-encoded value in input');
                    customButtonsInput.value = JSON.stringify(buttons);
                }
            } catch (e) {
                console.error('Error parsing custom buttons:', e);
                console.error('Failed value was:', customButtonsInput.value);
                console.error('Failed value substring:', customButtonsInput.value.substring(0, 100));
                // Reset to empty array and update the hidden input
                buttons = [];
                customButtonsInput.value = '[]';
            }

            function renderButtons() {
                customButtonsList.innerHTML = '';
                console.log('Rendering buttons:', buttons);
                buttons.forEach((btn, idx) => {
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = `
                        padding: 16px;
                        border: 1px solid rgba(255,255,255,0.1);
                        border-radius: 8px;
                        background-color: rgba(0,0,0,0.2);
                        margin-bottom: 12px;
                    `;

                    // Button header
                    const header = document.createElement('div');
                    header.style.cssText = `
                        margin-bottom: 12px;
                        font-weight: bold;
                        color: var(--primary-color);
                        font-size: 14px;
                    `;
                    header.textContent = `Button ${idx + 1}`;
                    wrapper.appendChild(header);

                    // URL field
                    const urlContainer = document.createElement('div');
                    urlContainer.style.marginBottom = '12px';
                    
                    const urlLabel = document.createElement('label');
                    urlLabel.style.cssText = `
                        display: block;
                        font-size: 11px;
                        margin-bottom: 4px;
                        opacity: 0.8;
                    `;
                    urlLabel.textContent = 'URL';
                    
                    const urlInput = document.createElement('input');
                    urlInput.type = 'text';
                    urlInput.placeholder = 'https://example.com or /page';
                    urlInput.value = btn.url || '';
                    urlInput.className = 'form-control';
                    urlInput.style.width = '100%';
                    
                    urlContainer.appendChild(urlLabel);
                    urlContainer.appendChild(urlInput);
                    wrapper.appendChild(urlContainer);

                    // Text field
                    const textContainer = document.createElement('div');
                    textContainer.style.marginBottom = '12px';
                    
                    const textLabel = document.createElement('label');
                    textLabel.style.cssText = `
                        display: block;
                        font-size: 11px;
                        margin-bottom: 4px;
                        opacity: 0.8;
                    `;
                    textLabel.textContent = 'Button Text';
                    
                    const nameInput = document.createElement('input');
                    nameInput.type = 'text';
                    nameInput.placeholder = 'Enter button text';
                    nameInput.value = btn.title || '';
                    nameInput.className = 'form-control';
                    nameInput.style.width = '100%';
                    
                    textContainer.appendChild(textLabel);
                    textContainer.appendChild(nameInput);
                    wrapper.appendChild(textContainer);

                    // Icon field
                    const iconContainer = document.createElement('div');
                    iconContainer.style.marginBottom = '12px';
                    
                    const iconLabel = document.createElement('label');
                    iconLabel.style.cssText = `
                        display: block;
                        font-size: 11px;
                        margin-bottom: 4px;
                        opacity: 0.8;
                    `;
                    iconLabel.textContent = 'Icon';
                    
                    const iconSelect = document.createElement('select');
                    iconSelect.className = 'form-control';
                    iconSelect.style.width = '100%';
                    fontAwesomeIcons.forEach(ic => {
                        const opt = document.createElement('option');
                        opt.value = `${ic.prefix} ${ic.class}`;
                        opt.textContent = ic.class;
                        if ((btn.icon || '') === opt.value) opt.selected = true;
                        iconSelect.appendChild(opt);
                    });
                    
                    iconContainer.appendChild(iconLabel);
                    iconContainer.appendChild(iconSelect);
                    wrapper.appendChild(iconContainer);

                    // Remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger';
                    removeBtn.textContent = 'Remove';
                    removeBtn.style.cssText = `
                        width: 100%;
                        margin-top: 4px;
                    `;
                    removeBtn.onclick = () => {
                        buttons.splice(idx, 1);
                        sync();
                        renderButtons();
                    };
                    wrapper.appendChild(removeBtn);

                    // Update handlers
                    urlInput.oninput = () => { buttons[idx].url = urlInput.value; sync(); };
                    nameInput.oninput = () => { buttons[idx].title = nameInput.value; sync(); };
                    iconSelect.onchange = () => { buttons[idx].icon = iconSelect.value; sync(); };

                    customButtonsList.appendChild(wrapper);
                });
            }

            function sync() {
                customButtonsInput.value = JSON.stringify(buttons);
            }

            addCustomButtonBtn.onclick = () => {
                buttons.push({ title: 'New Button', url: 'https://example.com', icon: 'fa-solid fa-link' });
                sync();
                renderButtons();
            };

            renderButtons();
        })();

    function populateIconList() {
        const iconList = document.getElementById('iconList');
        iconList.innerHTML = '';

        fontAwesomeIcons.forEach(iconData => {
            const iconItem = document.createElement('div');
            iconItem.className = 'icon-item';
            const fullClass = `${iconData.prefix} ${iconData.class}`;
            iconItem.innerHTML = `
                <i class="${fullClass}"></i>
                <span class="icon-class">${iconData.class}</span>
            `;
            iconItem.onclick = () => selectIcon(fullClass);
            iconList.appendChild(iconItem);
        });
    }

    function toggleIconDropdown() {
        const dropdown = document.getElementById('iconDropdown');
        const selector = document.querySelector('.icon-selector');
        const container = document.querySelector('.icon-selector-container');
        
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            selector.classList.remove('active');
            container.classList.remove('dropdown-active');
        } else {
            dropdown.classList.add('active');
            selector.classList.add('active');
            container.classList.add('dropdown-active');
            populateIconList();
            document.getElementById('iconSearch').focus();
        }
    }

    function selectIcon(iconClass) {
        const hiddenInput = document.getElementById('announcement_icon');
        const selectedIcon = document.querySelector('.selected-icon');
        const container = document.querySelector('.icon-selector-container');
        
        hiddenInput.value = iconClass;
        selectedIcon.innerHTML = `
            <i class="${iconClass}"></i>
            <span class="icon-name">${iconClass}</span>
        `;
        
        // Close dropdown
        document.getElementById('iconDropdown').classList.remove('active');
        document.querySelector('.icon-selector').classList.remove('active');
        container.classList.remove('dropdown-active');
    }

    function filterIcons(searchTerm) {
        const iconItems = document.querySelectorAll('.icon-item');
        const term = searchTerm.toLowerCase();

        iconItems.forEach(item => {
            const iconClass = item.querySelector('.icon-class').textContent.toLowerCase();
            if (iconClass.includes(term)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const container = document.querySelector('.icon-selector-container');
        if (container && !container.contains(event.target)) {
            document.getElementById('iconDropdown').classList.remove('active');
            document.querySelector('.icon-selector').classList.remove('active');
            container.classList.remove('dropdown-active');
        }
    });

    // Loading screen mode toggle
    document.addEventListener('DOMContentLoaded', function() {
        const loadingModeSelect = document.getElementById('loading_screen_mode');
        const timerDurationGroup = document.getElementById('timer-duration-group');
        
        function toggleDurationField() {
            if (loadingModeSelect.value === 'timer') {
                timerDurationGroup.style.display = 'flex';
            } else {
                timerDurationGroup.style.display = 'none';
            }
        }
        
        // Initial toggle
        toggleDurationField();
        
        // Listen for changes
        loadingModeSelect.addEventListener('change', toggleDurationField);
    });

</script>