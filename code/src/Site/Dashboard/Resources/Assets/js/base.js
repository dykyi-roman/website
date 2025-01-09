// Fetch and store settings - only available for authenticated users
async function fetchAndStoreSettings() {
    if (!window.appConfig?.isAuthenticated) {
        return;
    }

    try {
        const response = await fetch('/api/v1/settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch settings');
        }
        
        const settings = await response.json();

        setCookie('userSettings', JSON.stringify(settings.settings));
    } catch (error) {
        console.error('Error fetching settings:', error);
    }
}

// Execute fetchAndStoreSettings immediately if user is authenticated
fetchAndStoreSettings();

// Profile settings update function - only available for authenticated users
window.updateProfileSetting = async function(settings) {
    if (!window.appConfig?.isAuthenticated) {
        return;
    }

    // Handle both single setting and array of settings
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

        if (!response.ok) {
            throw new Error(data.message || 'Failed to update setting');
        }

        return data;
    } catch (error) {
        console.error('Error updating profile setting:', error);
        throw error;
    }
};

// Function to set cookie
function setCookie(name, value, days = 365) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    const expires = `expires=${date.toUTCString()}`;
    document.cookie = `${name}=${value};${expires};path=/`;
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Global error handling with spinner management
document.addEventListener('DOMContentLoaded', function() {
    // Add spinner overlay to all modals
    document.querySelectorAll('.modal .modal-content').forEach(modalContent => {
        const spinnerOverlay = document.createElement('div');
        spinnerOverlay.className = 'spinner-overlay';
        spinnerOverlay.innerHTML = '<div class="spinner"></div>';
        modalContent.appendChild(spinnerOverlay);
    });

    // Helper function to create spinner overlay if not exists
    function createSpinnerOverlay(element) {
        const spinnerOverlay = document.createElement('div');
        spinnerOverlay.className = 'spinner-overlay';
        spinnerOverlay.innerHTML = '<div class="spinner"></div>';
        
        const targetElement = element.closest('.modal-content') || element.closest('.modal') || element;
        targetElement.appendChild(spinnerOverlay);
        
        return spinnerOverlay;
    }

    // Global function to show spinner for any modal
    window.showModalSpinner = function(modalElement) {
        const spinnerOverlay = modalElement 
            ? modalElement.querySelector('.spinner-overlay') || 
              modalElement.closest('.modal')?.querySelector('.spinner-overlay') || 
              modalElement.closest('.modal-content')?.querySelector('.spinner-overlay') ||
              createSpinnerOverlay(modalElement)
            : document.querySelector('.modal.show .spinner-overlay');
            
        if (spinnerOverlay) {
            spinnerOverlay.classList.add('active');
        }
    };

    // Global function to hide spinner for any modal
    window.hideModalSpinner = function(modalElement) {
        const spinnerOverlay = modalElement 
            ? modalElement.querySelector('.spinner-overlay') || 
              modalElement.closest('.modal')?.querySelector('.spinner-overlay') || 
              modalElement.closest('.modal-content')?.querySelector('.spinner-overlay')
            : document.querySelector('.modal.show .spinner-overlay');
            
        if (spinnerOverlay) {
            spinnerOverlay.classList.remove('active');
        }
    };

    // Global error handler
    window.addEventListener('error', function(event) {
        hideModalSpinner();
        return false;
    });

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
        hideModalSpinner();
        return false;
    });

    // Global AJAX error handler for jQuery
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxError(function() {
            hideModalSpinner();
        });
    }

    // Intercept all form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const modal = form.closest('.modal-content');
        
        if (modal) {
            if (form.dataset.ajax === 'true') {
                e.preventDefault();
            }
        }
    }, true);

    // Intercept alert function
    const originalAlert = window.alert;
    window.alert = function() {
        hideModalSpinner();
        return originalAlert.apply(this, arguments);
    };

    // Intercept fetch API
    const originalFetch = window.fetch;
    window.fetch = function(input, init = {}) {
        // Add Accept header if not present
        init.headers = init.headers || {};
        if (typeof init.headers === 'object' && !(init.headers instanceof Headers)) {
            init.headers = new Headers(init.headers);
        }
        if (!init.headers.has('Accept')) {
            init.headers.set('Accept', 'application/json');
        }
        
        const fetchPromise = originalFetch.call(this, input, init);
        fetchPromise.catch(() => hideModalSpinner());
        return fetchPromise;
    };

    // Intercept XMLHttpRequest
    const originalXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function() {
        this.addEventListener('error', () => hideModalSpinner());
        this.addEventListener('abort', () => hideModalSpinner());
        return originalXHROpen.apply(this, arguments);
    };
});

// Create scroll to top button
document.addEventListener('DOMContentLoaded', function() {
    // Create the button element
    const scrollButton = document.createElement('div');
    scrollButton.className = 'scroll-to-top';
    scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    document.body.appendChild(scrollButton);

    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollButton.classList.add('visible');
        } else {
            scrollButton.classList.remove('visible');
        }
    });

    // Smooth scroll to top when button is clicked
    scrollButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

// Add back button functionality
document.addEventListener('DOMContentLoaded', function() {
    // Create the back button element
    const backButton = document.createElement('div');
    backButton.id = 'back-button';
    backButton.className = 'back-button';
    backButton.innerHTML = '<i class="fas fa-arrow-left"></i>';
    backButton.title = 'Go Back';
    
    // Append to body
    document.body.appendChild(backButton);
    
    // Show/hide back button based on page history and current path
    const currentPath = window.location.pathname;
    const shouldShowBackButton = currentPath !== '/';
    
    if (shouldShowBackButton) {
        backButton.classList.add('visible');
    }
    
    // Add click event listener
    backButton.addEventListener('click', function() {
        // Get current site's hostname
        const currentHostname = window.location.hostname;

        // Check browser history
        if (window.history.length > 1) {
            // Try to find a previous page within the same domain
            for (let i = window.history.length - 2; i >= 0; i--) {
                try {
                    // Use history.go with negative index to check previous pages
                    const previousState = window.history.state;
                    const previousUrl = previousState?.url || document.referrer;

                    if (previousUrl) {
                        const previousUrlObj = new URL(previousUrl, window.location.origin);
                        
                        // Check if previous URL is from the same domain
                        if (previousUrlObj.hostname === currentHostname) {
                            window.history.go(-1);
                            return;
                        }
                    }
                } catch (error) {
                    console.warn('Error checking previous page:', error);
                }
            }

            // If no same-domain page found, go to homepage
            window.location.href = '/';
        } else {
            // If no history, go to homepage
            window.location.href = '/';
        }
    });
});

// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.password-wrapper').querySelector('input');
            
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        });
    });
});

// Theme switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Function to toggle theme
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        // Set theme attribute
        document.documentElement.setAttribute('data-theme', newTheme);

        // Update theme icon
        updateThemeIcon(newTheme);

        // Save theme to cookie
        setCookie('theme', newTheme);

        // Update theme-select if it exists
        const themeSelect = document.getElementById('theme-select');
        const themeSelectValue = newTheme === 'dark' ? 'dark-theme' : 'light-theme';
        if (themeSelect) {
            themeSelect.value = themeSelectValue;
        }

        // Only update profile settings if user is authenticated
        if (window.appConfig?.isAuthenticated) {
            updateProfileSetting([['GENERAL', 'theme', themeSelectValue]]).catch(error => console.error('Failed to update theme:', error));
        }

        // Dispatch theme change event
        document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: newTheme } }));
    }

    // Function to update theme icon
    function updateThemeIcon(theme) {
        themeIcon.className = `fas ${theme === 'dark' ? 'fa-sun' : 'fa-moon'}`;
    }

    // Check for saved theme on page load
    const savedTheme = getCookie('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    // Update theme-select if it exists on page load
    const themeSelect = document.getElementById('theme-select');
    if (themeSelect) {
        themeSelect.value = savedTheme === 'dark' ? 'dark-theme' : 'light-theme';
    }

    // Add click event listener to theme toggle
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});

// Initialize settings on page load
document.addEventListener('DOMContentLoaded', function() {
    // Fetch settings immediately when page loads
    fetchAndStoreSettings();
    
    // Function to handle cookie consent
    function handleCookieConsent() {
        const cookieConsent = document.getElementById('cookieConsent');
        if (!cookieConsent) return;

        const bsOffcanvas = new bootstrap.Offcanvas(cookieConsent);
        const agreementCookie = getCookie('agreement_with_cookies');

        // Show consent popup if cookie doesn't exist and display is required
        if (!agreementCookie) {
            bsOffcanvas.show();
        }

        // Handle accept button click
        document.getElementById('acceptCookies')?.addEventListener('click', function() {
            setCookie('agreement_with_cookies', '1');
            updateProfileSetting([['GENERAL', 'cookies', 1]]).catch(error => console.error('Failed to update cookies:', error));
            bsOffcanvas.hide();
        });

        // Handle reject button click
        document.getElementById('rejectCookies')?.addEventListener('click', function() {
            setCookie('agreement_with_cookies', '0');
            updateProfileSetting([['GENERAL', 'cookies', 0]]).catch(error => console.error('Failed to update cookies:', error));
            bsOffcanvas.hide();
        });
    }

    // Initialize cookie consent handling
    handleCookieConsent();
});

// Get geolocation from browser
document.addEventListener('DOMContentLoaded', function() {
    async function getCountryWithCache(latitude, longitude) {
        const roundedLat = Math.round(latitude * 100) / 100;
        const roundedLon = Math.round(longitude * 100) / 100;

        const cacheKey = `country_${roundedLat}_${roundedLon}`;
        const cached = localStorage.getItem(cacheKey);

        if (cached) {
            return JSON.parse(cached);
        }

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`;
            const response = await fetch(url);
            const data = await response.json();

            const result = {
                country: data.address.country,
                countryCode: data.address.country_code,
                city: data.address.city
            };

            setCookie('appCountry', JSON.stringify(result));
            localStorage.setItem(cacheKey, JSON.stringify(result));
            console.log(result);
        } catch (error) {
            console.error("Error in determining country:", error);
            throw error;
        }
    }

    navigator.geolocation.getCurrentPosition(async position => {
        try {
            await getCountryWithCache(position.coords.latitude, position.coords.longitude);
        } catch (error) {
            console.error("Error:", error);
        }
    });
});

// Check and set referral code
(function() {
    function setReferralCode() {
        // Check if reff cookie already exists
        if (getCookie('reff')) {
            return;
        }

        // Get reff parameter from URL
        const urlParams = new URLSearchParams(window.location.search);
        const reffCode = urlParams.get('reff');

        // If reff parameter exists in URL, set it as a cookie
        if (reffCode) {
            // Set cookie with 30 days expiration
            const expirationDate = new Date();
            expirationDate.setDate(expirationDate.getDate() + 30);
            document.cookie = `reff=${reffCode}; expires=${expirationDate.toUTCString()}; path=/`;
        }
    }

    // Run when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setReferralCode);
    } else {
        setReferralCode();
    }
})();
