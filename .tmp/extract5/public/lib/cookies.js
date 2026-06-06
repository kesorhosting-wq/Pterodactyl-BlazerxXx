document.addEventListener('DOMContentLoaded', function () {
    const cookieAlert = document.getElementById('cookie-alert');

    // Check if the cookie alert is enabled and on the home page
    if (window.location.pathname === '/') {
        const cookieAccepted = localStorage.getItem('cookieAccepted');
        if (!cookieAccepted) {
            cookieAlert.style.display = 'block'; // Show cookie alert if not accepted
        }
    }

    // Accept Cookies Functionality
    const acceptCookiesButton = document.getElementById('accept-cookies');
    if (acceptCookiesButton) {
        acceptCookiesButton.addEventListener('click', function () {
            localStorage.setItem('cookieAccepted', 'true'); // Save acceptance
            cookieAlert.style.display = 'none'; // Hide the cookie alert
        });
    }
});