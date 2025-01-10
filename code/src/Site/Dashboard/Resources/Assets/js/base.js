// Services
class CookieService {
    static set(name, value, days = 365) {
        try {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
            return true;
        } catch (error) {
            ErrorService.handle('Error setting cookie:', error);
            return false;
        }
    }

    static get(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        return parts.length === 2 ? parts.pop().split(';').shift() : null;
    }
}

class ErrorService {
    static handle(message, error) {
        console.error(message, error);
        ModalService.hideSpinner();
    }

    static init() {
        window.addEventListener('error', () => ModalService.hideSpinner());
        window.addEventListener('unhandledrejection', () => ModalService.hideSpinner());
        
        // Intercept alert
        const originalAlert = window.alert;
        window.alert = function() {
            ModalService.hideSpinner();
            return originalAlert.apply(this, arguments);
        };
    }
}

class ApiService {
    static async updateSettings(settings) {
        if (!ConfigService.isAuthenticated) return;

        const settingsArray = Array.isArray(settings) ? settings : [[...arguments]];
        
        try {
            const response = await fetch('/api/v1/settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    settings: settingsArray.map(([category, name, value]) => ({
                        category,
                        name,
                        value
                    }))
                })
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to update setting');
            return data;
        } catch (error) {
            ErrorService.handle('Error updating settings:', error);
            throw error;
        }
    }

    static async getSettings() {
        try {
            const response = await fetch('/api/v1/settings', {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to fetch settings');
            return await response.json();
        } catch (error) {
            ErrorService.handle('Error fetching settings:', error);
            throw error;
        }
    }

    static async getCountryFromCoords(latitude, longitude) {
        const roundedLat = Math.round(latitude * 100) / 100;
        const roundedLon = Math.round(longitude * 100) / 100;
        const cacheKey = `country_${roundedLat}_${roundedLon}`;
        
        const cached = localStorage.getItem(cacheKey);
        if (cached) return JSON.parse(cached);

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`
            );
            const data = await response.json();

            const result = {
                country: data.address.country,
                countryCode: data.address.country_code,
                city: data.address.city
            };

            CookieService.set('appCountry', JSON.stringify(result));
            localStorage.setItem(cacheKey, JSON.stringify(result));
            return result;
        } catch (error) {
            ErrorService.handle('Error determining country:', error);
            throw error;
        }
    }
}

class ThemeService {
    static #initialized = false;
    static #themeToggle = null;
    static #themeIcon = null;

    static init(initialTheme = 'light') {
        if (this.#initialized) return;
        this.#initialized = true;

        this.#themeToggle = document.getElementById('themeToggle');
        this.#themeIcon = document.getElementById('themeIcon');

        this.setTheme(initialTheme);
        
        if (this.#themeToggle) {
            this.#themeToggle.addEventListener('click', () => this.toggle());
        }
    }

    static setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        if (this.#themeIcon) {
            this.#themeIcon.className = `fas ${theme === 'dark' ? 'fa-sun' : 'fa-moon'}`;
        }

        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.value = theme;
        }

        CookieService.set('appTheme', theme);

        if (ConfigService.isAuthenticated) {
            ApiService.updateSettings([['GENERAL', 'theme', theme]])
                .catch(error => ErrorService.handle('Failed to update theme:', error));
        }

        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    static toggle() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        this.setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    }
}

class ModalService {
    static init() {
        document.querySelectorAll('.modal .modal-content').forEach(modalContent => {
            this.#createSpinnerOverlay(modalContent);
        });

        // Intercept form submissions
        document.addEventListener('submit', e => {
            const form = e.target;
            const modal = form.closest('.modal-content');
            if (modal && form.dataset.ajax === 'true') {
                e.preventDefault();
            }
        }, true);
    }

    static #createSpinnerOverlay(element) {
        const spinnerOverlay = document.createElement('div');
        spinnerOverlay.className = 'spinner-overlay';
        spinnerOverlay.innerHTML = '<div class="spinner"></div>';

        const targetElement = element.closest('.modal-content') || element.closest('.modal') || element;
        targetElement.appendChild(spinnerOverlay);

        return spinnerOverlay;
    }

    static showSpinner(modalElement) {
        const spinnerOverlay = modalElement
            ? modalElement.querySelector('.spinner-overlay') ||
              modalElement.closest('.modal')?.querySelector('.spinner-overlay') ||
              modalElement.closest('.modal-content')?.querySelector('.spinner-overlay') ||
              this.#createSpinnerOverlay(modalElement)
            : document.querySelector('.modal.show .spinner-overlay');

        if (spinnerOverlay) {
            spinnerOverlay.classList.add('active');
        }
    }

    static hideSpinner(modalElement) {
        const spinnerOverlay = modalElement
            ? modalElement.querySelector('.spinner-overlay') ||
              modalElement.closest('.modal')?.querySelector('.spinner-overlay') ||
              modalElement.closest('.modal-content')?.querySelector('.spinner-overlay')
            : document.querySelector('.modal.show .spinner-overlay');

        if (spinnerOverlay) {
            spinnerOverlay.classList.remove('active');
        }
    }
}

class UIService {
    static initScrollToTop() {
        const scrollButton = document.createElement('div');
        scrollButton.className = 'scroll-to-top';
        scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(scrollButton);

        window.addEventListener('scroll', () => {
            scrollButton.classList.toggle('visible', window.pageYOffset > 300);
        });

        scrollButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    static initBackButton() {
        const backButton = document.createElement('div');
        backButton.id = 'back-button';
        backButton.className = 'back-button';
        backButton.innerHTML = '<i class="fas fa-arrow-left"></i>';
        backButton.title = 'Go Back';
        
        document.body.appendChild(backButton);
        
        if (window.location.pathname !== '/') {
            backButton.classList.add('visible');
        }
        
        backButton.addEventListener('click', () => {
            const currentHostname = window.location.hostname;

            if (window.history.length > 1) {
                try {
                    const previousState = window.history.state;
                    const previousUrl = previousState?.url || document.referrer;

                    if (previousUrl) {
                        const previousUrlObj = new URL(previousUrl, window.location.origin);
                        if (previousUrlObj.hostname === currentHostname) {
                            window.history.go(-1);
                            return;
                        }
                    }
                } catch (error) {
                    ErrorService.handle('Error checking previous page:', error);
                }
            }
            window.location.href = '/';
        });
    }

    static initPasswordToggles() {
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.password-wrapper').querySelector('input');
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    }

    static initCookieConsent() {
        const cookieConsent = document.getElementById('cookieConsent');
        if (!cookieConsent) return;

        const bsOffcanvas = new bootstrap.Offcanvas(cookieConsent);
        if (!CookieService.get('agreement_with_cookies')) {
            bsOffcanvas.show();
        }

        document.getElementById('acceptCookies')?.addEventListener('click', () => {
            CookieService.set('agreement_with_cookies', '1');
            ApiService.updateSettings([['GENERAL', 'cookies', 1]])
                .catch(error => ErrorService.handle('Failed to update cookies:', error));
            bsOffcanvas.hide();
        });

        document.getElementById('rejectCookies')?.addEventListener('click', () => {
            CookieService.set('agreement_with_cookies', '0');
            ApiService.updateSettings([['GENERAL', 'cookies', 0]])
                .catch(error => ErrorService.handle('Failed to update cookies:', error));
            bsOffcanvas.hide();
        });
    }
}

class ConfigService {
    static get isAuthenticated() {
        return window.appConfig?.isAuthenticated ?? false;
    }
}

class GeolocationService {
    static init() {
        navigator.geolocation.getCurrentPosition(async position => {
            try {
                await ApiService.getCountryFromCoords(
                    position.coords.latitude,
                    position.coords.longitude
                );
            } catch (error) {
                ErrorService.handle('Geolocation error:', error);
            }
        });
    }
}

class ReferralService {
    static init() {
        if (CookieService.get('reff')) return;

        const urlParams = new URLSearchParams(window.location.search);
        const reffCode = urlParams.get('reff');
        if (reffCode) {
            CookieService.set('reff', reffCode, 30);
        }
    }
}

// Initialize application
document.addEventListener('DOMContentLoaded', async () => {
    // Initialize core services
    ErrorService.init();
    ModalService.init();
    
    // Initialize UI components
    UIService.initScrollToTop();
    UIService.initBackButton();
    UIService.initPasswordToggles();
    UIService.initCookieConsent();
    
    // Initialize geolocation and referral
    GeolocationService.init();
    ReferralService.init();

    // Initialize theme
    const defaultTheme = 'light';
    if (!ConfigService.isAuthenticated) {
        const savedTheme = CookieService.get('appTheme');
        ThemeService.init(savedTheme || defaultTheme);
        return;
    }

    try {
        const data = await ApiService.getSettings();
        if (data.settings?.general) {
            const { theme, language, currency } = data.settings.general;

            ThemeService.init(theme || CookieService.get('appTheme') || defaultTheme);
            
            if (language) {
                CookieService.set('locale', language);
            }
            
            if (currency) {
                CookieService.set('appCurrency', currency.toUpperCase());
                // Dispatch currency change event
                document.dispatchEvent(new CustomEvent('currencyChanged', { 
                    detail: { currency: currency.toUpperCase() } 
                }));
            }
        } else {
            const savedTheme = CookieService.get('appTheme');
            ThemeService.init(savedTheme || defaultTheme);
        }
    } catch {
        const savedTheme = CookieService.get('appTheme');
        ThemeService.init(savedTheme || defaultTheme);
    }
});

// Export global functions for backward compatibility
window.setCookie = CookieService.set.bind(CookieService);
window.getCookie = CookieService.get.bind(CookieService);
window.updateProfileSetting = ApiService.updateSettings;
window.showModalSpinner = ModalService.showSpinner.bind(ModalService);
window.hideModalSpinner = ModalService.hideSpinner.bind(ModalService);
