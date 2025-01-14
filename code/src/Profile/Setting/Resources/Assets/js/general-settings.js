/**
 * @typedef {Object} SettingsState
 * @property {string|null} language - Selected language
 * @property {string|null} currency - Selected currency
 * @property {string|null} theme - Selected theme
 */

/**
 * Class managing general settings functionality
 */
class GeneralSettingsManager {
    /** @type {HTMLSelectElement|null} */
    #languageSelect;
    /** @type {HTMLSelectElement|null} */
    #currencySelect;
    /** @type {HTMLSelectElement|null} */
    #themeSelect;
    /** @type {HTMLButtonElement|null} */
    #saveButton;
    /** @type {SettingsState} */
    #initialValues;
    /** @type {SettingsState} */
    #pendingChanges;

    /**
     * Initialize the settings manager
     */
    constructor() {
        this.#initializeElements();
        this.#initializeState();
        this.#setupEventListeners();
        this.#applyInitialTheme();
    }

    /**
     * Initialize DOM elements
     * @private
     */
    #initializeElements() {
        this.#languageSelect = document.getElementById('language-select');
        this.#currencySelect = document.getElementById('currency-select');
        this.#themeSelect = document.getElementById('theme-select');
        this.#saveButton = document.getElementById('save-general-settings');
    }

    /**
     * Initialize state management
     * @private
     */
    #initializeState() {
        this.#initialValues = {
            language: this.#languageSelect?.value ?? null,
            currency: this.#currencySelect?.value ?? null,
            theme: this.#themeSelect?.value ?? null
        };

        this.#pendingChanges = {
            language: null,
            currency: null,
            theme: null
        };
    }

    /**
     * Set up event listeners for form elements
     * @private
     */
    #setupEventListeners() {
        this.#setupSelectListener(this.#languageSelect, 'language');
        this.#setupSelectListener(this.#currencySelect, 'currency');
        this.#setupSelectListener(this.#themeSelect, 'theme');
        this.#setupSaveButton();
    }

    /**
     * Set up event listener for a select element
     * @private
     * @param {HTMLSelectElement|null} element - Select element
     * @param {keyof SettingsState} key - State key to update
     */
    #setupSelectListener(element, key) {
        element?.addEventListener('change', (e) => {
            this.#pendingChanges[key] = e.target.value;
            this.#updateSaveButtonState();
        });
    }

    /**
     * Set up save button functionality
     * @private
     */
    #setupSaveButton() {
        if (!this.#saveButton) return;

        this.#updateSaveButtonState();
        this.#saveButton.addEventListener('click', async (e) => {
            e.preventDefault();
            await this.#handleSave();
        });
    }

    /**
     * Apply initial theme settings
     * @private
     */
    #applyInitialTheme() {
        if (!this.#themeSelect) return;

        const savedTheme = CookieService.get('appTheme') || 'light';
        this.#themeSelect.value = savedTheme;
        this.#toggleTheme(savedTheme);
    }

    /**
     * Check if any values have changed from initial state
     * @private
     * @returns {boolean}
     */
    #hasChanges() {
        return Object.entries(this.#pendingChanges).some(([key, value]) => 
            value !== null && value !== this.#initialValues[key]
        );
    }

    /**
     * Update save button state based on changes
     * @private
     */
    #updateSaveButtonState() {
        if (this.#saveButton) {
            this.#saveButton.disabled = !this.#hasChanges();
        }
    }

    /**
     * Toggle theme and update related elements
     * @private
     * @param {string} theme - Theme to apply
     */
    #toggleTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        const themeToggleButton = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');

        if (themeToggleButton && themeIcon) {
            themeIcon.className = `fas ${theme === 'dark' ? 'fa-sun' : 'fa-moon'}`;
        }

        CookieService.set('appTheme', theme);
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    /**
     * Handle save operation
     * @private
     * @returns {Promise<void>}
     */
    async #handleSave() {
        try {
            const settings = [];
            const needsReload = { shouldReload: false, language: null };
            const { language, currency, theme } = this.#pendingChanges;

            // Collect settings and handle cookies
            if (language) {
                CookieService.set('locale', language);
                settings.push(['GENERAL', 'language', language]);
                needsReload.shouldReload = true;
                needsReload.language = language;
            }

            if (currency) {
                CookieService.set('appCurrency', currency.toUpperCase());
                settings.push(['GENERAL', 'currency', currency]);
                if (!needsReload.language) { // Only set if language change isn't present
                    needsReload.shouldReload = true;
                }
            }

            if (theme) {
                this.#toggleTheme(theme);
                settings.push(['GENERAL', 'theme', theme]);
            }

            // Save settings if there are any changes
            if (settings.length > 0) {
                await updateProfileSetting(settings);
                
                // Handle page reload before updating state
                if (needsReload.shouldReload) {
                    if (needsReload.language) {
                        const url = new URL(window.location.href);
                        url.searchParams.set('lang', needsReload.language);
                        window.location.href = url.toString();
                        return; // Stop execution as page will reload
                    } else {
                        window.location.reload();
                        return; // Stop execution as page will reload
                    }
                }

                // Update state if no reload is needed
                this.#updateStateAfterSave();
            }
        } catch (error) {
            console.error('Failed to save settings:', error);
            // Here you might want to show a user-friendly error message
        }
    }

    /**
     * Update state after successful save
     * @private
     */
    #updateStateAfterSave() {
        // Update initial values with saved changes
        Object.entries(this.#pendingChanges).forEach(([key, value]) => {
            if (value !== null) {
                this.#initialValues[key] = value;
            }
        });

        // Reset pending changes
        this.#pendingChanges = {
            language: null,
            currency: null,
            theme: null
        };

        this.#updateSaveButtonState();
    }
}

// Initialize settings manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new GeneralSettingsManager();
});
