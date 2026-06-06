document.addEventListener("DOMContentLoaded", function () {
    const licenseForm = document.getElementById("license-form");

    if (!licenseForm) {
        console.error("License form not found in the DOM.");
        return;
    }

    licenseForm.addEventListener("submit", async function (event) {
        event.preventDefault(); // Prevent the default form submission

        const licenseKey = document.getElementById("licenseKey").value;
        const hwid = document.querySelector('input[name="hwid"]').value;
        const productId = document.querySelector('input[name="productId"]').value;
        const source = window.location.origin; // Get the current domain

        if (!licenseKey) {
            alert("Please enter a license key.");
            return;
        }

        try {
            const response = await fetch("https://api.euphoriadevelopment.uk/license/verify-license", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ licenseKey, productId, hwid, source }),
            });

            const data = await response.json();

            if (data.success) {
                alert("License key is valid!");
                // Submit the form programmatically
                licenseForm.submit();
            } else {
                alert("Invalid License Key: " + data.error);
            }
        } catch (error) {
            alert("Error verifying license key. Please try again.");
            console.error("API error:", error);
        }
    });
});