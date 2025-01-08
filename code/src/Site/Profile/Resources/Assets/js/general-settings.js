document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language-select');
    const currencySelect = document.getElementById('currency-select');
    const themeSelect = document.getElementById('theme-select');

    // Theme Toggle Function
    function toggleTheme(theme) {
        // Determine theme name (base.js uses 'dark' and 'light')
        const themeName = theme === 'dark-theme' ? 'dark' : 'light';

        // Update document class
        document.documentElement.setAttribute('data-theme', themeName);

        // Update theme icon if exists
        const themeToggleButton = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (themeToggleButton && themeIcon) {
            themeIcon.className = `fas ${themeName === 'dark' ? 'fa-sun' : 'fa-moon'}`;
        }

        // Set cookie for theme
        setCookie('theme', themeName);

        // Trigger any theme-related event listeners
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeName } }));
    }

    // Utility function to set cookie
    function setCookie(name, value, days = 365) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
    }

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
        // Get current theme from cookie or default
        const savedTheme = getCookie('theme') || 'light';
        const themeValue = savedTheme === 'dark' ? 'dark-theme' : 'light-theme';
        themeSelect.value = themeValue;

        themeSelect.addEventListener('change', function(e) {
            const themeName = this.value;
            toggleTheme(themeName);

            updateProfileSetting('GENERAL', 'theme', 'string', $(this).val())
                .catch(error => console.error('Failed to update theme:', error));
        });

        // Trigger initial theme setup
        toggleTheme(themeValue);
    }

    // Utility function to get cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
});
