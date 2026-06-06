
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div id="language-selector-container" class="no-translate" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
    <button id="language-selector-button" class="no-translate">
        <i class="fa-solid fa-language"></i>
    </button>
    <div id="language-dropdown" class="no-translate" style="display: none; position: absolute; top: 40px; overflow-y: scroll; height: auto; max-height: 30vh; padding: 0.5rem; margin-top: 1.5vh; right: 0; background-color: hsla(0, 0%, 0%, 0.8) !important; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border: rgba(255, 255, 255, 0.07843); border-style: solid;">
        <ul style="list-style: none; margin: 0; padding: 10px; max-height: 200px; overflow-y: auto;">
        </ul>
    </div>
    <!-- Tooltip for current language -->
    <div id="language-tooltip" class="no-translate" style="display: none; position: absolute; top: 50%; right: 110%; transform: translateY(-50%); background-color: hsla(0, 0%, 0%, 0.8); color: white; padding: 5px 10px; border-radius: 5px; font-size: 12px; opacity: 0; transition: opacity 0.5s; border: rgba(255, 255, 255, 0.07843); border-style: solid;">
        <!-- Tooltip text will be dynamically set -->
    </div>
</div>