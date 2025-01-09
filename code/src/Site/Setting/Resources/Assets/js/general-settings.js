document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language-select');
    const currencySelect = document.getElementById('currency-select');
    const themeSelect = document.getElementById('theme-select');
    const saveButton = document.getElementById('save-settings');
    
    // Store pending changes
    let pendingChanges = {
        language: null,
        currency: null,
        theme: null
    };

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
            pendingChanges.language = this.value;
            saveButton.disabled = false;
        });
    }

    // Currency Selection
    if (currencySelect) {
        currencySelect.addEventListener('change', function(e) {
            pendingChanges.currency = this.value;
            saveButton.disabled = false;
        });
    }

    // Theme Selection
    if (themeSelect) {
        // Get current theme from cookie or default
        const savedTheme = getCookie('theme') || 'light';
        const themeValue = savedTheme === 'dark' ? 'dark-theme' : 'light-theme';
        themeSelect.value = themeValue;

        themeSelect.addEventListener('change', function(e) {
            pendingChanges.theme = this.value;
            saveButton.disabled = false;
        });

        // Set initial theme
        toggleTheme(themeValue);
    }

    // Save Button Handler
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.addEventListener('click', async function() {
            try {
                // Collect all pending changes
                const settings = [];
                
                if (pendingChanges.language) {
                    // Set language cookie
                    const expirationDate = new Date();
                    expirationDate.setFullYear(expirationDate.getFullYear() + 1);
                    document.cookie = `locale=${pendingChanges.language}; expires=${expirationDate.toUTCString()}; path=/; SameSite=Strict`;
                    
                    settings.push(['GENERAL', 'language', pendingChanges.language]);
                }
                
                if (pendingChanges.currency) {
                    // Set currency cookie
                    const expirationDate = new Date();
                    expirationDate.setFullYear(expirationDate.getFullYear() + 1);
                    document.cookie = `appCurrency=${pendingChanges.currency}; expires=${expirationDate.toUTCString()}; path=/`;
                    
                    settings.push(['GENERAL', 'currency', pendingChanges.currency]);
                }
                
                if (pendingChanges.theme) {
                    toggleTheme(pendingChanges.theme);
                    settings.push(['GENERAL', 'theme', pendingChanges.theme]);
                }

                // Update all settings at once
                if (settings.length > 0) {
                    await updateProfileSetting(settings);
                }

                // Reload page to apply changes
                if (pendingChanges.language) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('lang', pendingChanges.language);
                    window.location.href = url.toString();
                } else if (pendingChanges.currency) {
                    window.location.reload();
                }

                saveButton.disabled = true;
                
            } catch (error) {
                console.error('Failed to save settings:', error);
                // You might want to show an error message to the user here
            }
        });
    }

    // Utility function to get cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
});
