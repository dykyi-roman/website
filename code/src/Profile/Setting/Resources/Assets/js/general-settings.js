document.addEventListener('DOMContentLoaded', function() {
    const languageSelect = document.getElementById('language-select');
    const currencySelect = document.getElementById('currency-select');
    const themeSelect = document.getElementById('theme-select');
    const saveButton = document.getElementById('save-general-settings');
    
    // Store pending changes
    let pendingChanges = {
        language: null,
        currency: null,
        theme: null
    };

    // Theme Toggle Function
    function toggleTheme(theme) {
        // Update document class
        document.documentElement.setAttribute('data-theme', theme);

        // Update theme icon if exists
        const themeToggleButton = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (themeToggleButton && themeIcon) {
            themeIcon.className = `fas ${theme === 'dark' ? 'fa-sun' : 'fa-moon'}`;
        }

        window.setCookie('appTheme', theme);

        // Trigger any theme-related event listeners
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: theme } }));
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
        const savedTheme = getCookie('appTheme') || 'light';
        themeSelect.value = savedTheme;

        themeSelect.addEventListener('change', function(e) {
            pendingChanges.theme = this.value;
            saveButton.disabled = false;
        });

        // Set initial theme
        toggleTheme(savedTheme);
    }

    // Save Button Handler
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.addEventListener('click', async function() {
            try {
                // Collect all pending changes
                const settings = [];
                
                if (pendingChanges.language) {
                    window.setCookie('locale', pendingChanges.language);
                    settings.push(['GENERAL', 'language', pendingChanges.language]);
                }
                
                if (pendingChanges.currency) {
                    window.setCookie('appCurrency', pendingChanges.currency.toUpperCase());
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
            }
        });
    }
});
