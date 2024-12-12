// Language menu selected
document.addEventListener('DOMContentLoaded', function() {
    const languageDropdown = document.getElementById('languageDropdown');
    const dropdownMenu = languageDropdown.nextElementSibling;

    languageDropdown.addEventListener('click', function() {
        // Get the current language
        const currentLanguage = this.querySelector('.language-code').textContent.toLowerCase();

        // Remove active class from all dropdown items
        dropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to the current language item
        const activeLanguageItem = dropdownMenu.querySelector(`[data-lang="${currentLanguage}"]`);
        if (activeLanguageItem) {
            activeLanguageItem.classList.add('active');
        }
    });
});

// Language Selection Handling
document.addEventListener('DOMContentLoaded', function() {
    const languageDropdownItems = document.querySelectorAll('.language-dropdown .dropdown-item');
    const languageCodeSpan = document.querySelector('.language-code');

    // Function to set cookie
    function setCookie(name, value, days = 30) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Strict`;
    }

    // Function to get cookie
    function getCookie(name) {
        const cookieName = name + "=";
        const decodedCookie = decodeURIComponent(document.cookie);
        const cookieArray = decodedCookie.split(';');
        for(let i = 0; i <cookieArray.length; i++) {
            let cookie = cookieArray[i];
            while (cookie.charAt(0) === ' ') {
                cookie = cookie.substring(1);
            }
            if (cookie.indexOf(cookieName) === 0) {
                return cookie.substring(cookieName.length, cookie.length);
            }
        }
        return "";
    }

    // Function to set language
    function setLanguage(selectedLang) {
        // Save language to localStorage
        localStorage.setItem('locale', selectedLang);

        // Save language to cookie
        setCookie('locale', selectedLang);

        // Update URL without losing existing parameters
        const url = new URL(window.location.href);
        const searchParams = url.searchParams;

        // Set or update lang parameter
        searchParams.set('lang', selectedLang);

        // Update document language attribute
        document.documentElement.lang = selectedLang;

        // Update language code in dropdown
        if (languageCodeSpan) {
            languageCodeSpan.textContent = selectedLang.toUpperCase();
        }

        // Reload page with new language
        window.location.href = url.toString();
    }

    // Language dropdown event listeners
    languageDropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedLang = this.getAttribute('data-lang');
            setLanguage(selectedLang);
        });
    });

    // Set initial language on page load
    function initializeLanguage() {
        // Priority:
        // 1. localStorage
        // 2. Cookie
        // 3. URL parameter
        // 4. Browser language
        // 5. Default to 'en'
        const urlParams = new URLSearchParams(window.location.search);
        const urlLang = urlParams.get('lang');
        const storedLang = localStorage.getItem('locale');
        const cookieLang = getCookie('locale');
        const browserLang = navigator.language.split('-')[0];

        let finalLang = 'en'; // default
        if (storedLang) {
            finalLang = storedLang;
        } else if (cookieLang) {
            finalLang = cookieLang;
            // Sync localStorage with cookie
            localStorage.setItem('locale', finalLang);
        } else if (urlLang) {
            finalLang = urlLang;
        } else if (browserLang) {
            finalLang = browserLang;
        }

        // Set document language
        document.documentElement.lang = finalLang;

        // Update language code span
        if (languageCodeSpan) {
            languageCodeSpan.textContent = finalLang.toUpperCase();
        }

        // Ensure cookie is set
        setCookie('locale', finalLang);
    }

    // Initialize language on page load
    initializeLanguage();
});

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
    window.fetch = function() {
        const fetchPromise = originalFetch.apply(this, arguments);
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
    
    // Log for debugging
    console.log('Back button created');
    console.log('Current pathname:', window.location.pathname);
    
    // Show/hide back button based on page history and current path
    const backButtonPages = ['/contact', '/privacy', '/terms'];
    const currentPath = window.location.pathname;
    
    // Check if current path matches any of the specified pages
    const shouldShowBackButton = backButtonPages.some(page => currentPath.includes(page));
    
    if (shouldShowBackButton) {
        backButton.classList.add('visible');
        console.log('Back button should be visible');
    } else {
        console.log('Back button should be hidden');
    }
    
    // Add click event listener
    backButton.addEventListener('click', function() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            // Fallback to homepage if no previous page
            window.location.href = '/';
        }
    });
});
