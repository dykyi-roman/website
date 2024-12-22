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
    console.log('Scroll button created');

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
    
    // Log for debugging
    console.log('Back button created');
    console.log('Current pathname:', window.location.pathname);
    
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
    const themeToggle = document.querySelector('.theme-toggle');
    const themeIcon = themeToggle.querySelector('i');
    
    // Check for saved theme preference, otherwise use system preference
    const savedTheme = localStorage.getItem('theme');
    const defaultTheme = savedTheme || 'light';

    // Set initial theme
    document.documentElement.setAttribute('data-theme', defaultTheme);
    updateThemeIcon(defaultTheme);

    // Add click event listener to existing theme toggle button
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update theme
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });

    function updateThemeIcon(theme) {
        themeIcon.className = `fas ${theme === 'dark' ? 'fa-sun' : 'fa-moon'}`;
    }
});

// Check and set referral code
(function() {
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

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