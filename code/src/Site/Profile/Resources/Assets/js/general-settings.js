document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language-select');
    const currencySelect = document.getElementById('currency-select');
    const themeSelect = document.getElementById('theme-select');

    // Language Selection
    if (languageSelect) {
        languageSelect.addEventListener('change', function(e) {
            const langCode = this.value;
            
            // Set cookie with 1 year expiration
            const expirationDate = new Date();
            expirationDate.setFullYear(expirationDate.getFullYear() + 1);
            document.cookie = `locale=${langCode}; expires=${expirationDate.toUTCString()}; path=/; SameSite=Strict`;

            // Update URL and reload page
            const url = new URL(window.location.href);
            url.searchParams.set('lang', langCode);
            window.location.href = url.toString();
        });
    }

    // Currency Selection
    if (currencySelect) {
        currencySelect.addEventListener('change', function(e) {
            const currencyCode = this.value;

            // Set cookie with 1 year expiration
            const expirationDate = new Date();
            expirationDate.setFullYear(expirationDate.getFullYear() + 1);
            document.cookie = `appCurrency=${currencyCode}; expires=${expirationDate.toUTCString()}; path=/`;

            // Reload page to apply new currency
            window.location.reload();
        });
    }

    // Theme Selection
    if (themeSelect) {
        themeSelect.addEventListener('change', function(e) {
            const themeName = this.value;

            // Set cookie with 1 year expiration
            const expirationDate = new Date();
            expirationDate.setFullYear(expirationDate.getFullYear() + 1);
            document.cookie = `appTheme=${themeName}; expires=${expirationDate.toUTCString()}; path=/`;

            // Apply theme immediately
            document.documentElement.className = themeName;
        });
    }
});
