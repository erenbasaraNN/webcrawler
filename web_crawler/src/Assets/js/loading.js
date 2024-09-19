document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('loadingForm');
    const domainInput = document.getElementById('domainInput');

    if (form && domainInput) {
        // Get the domain from the URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const domain = urlParams.get('domain');

        if (domain) {
            // Set the domain value in the hidden input
            domainInput.value = domain;

            // Submit the form
            form.submit();
        } else {
            console.error('No domain parameter found in URL');
        }
    }
});