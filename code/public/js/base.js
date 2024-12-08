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
        for(let i = 0; i < cookieArray.length; i++) {
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
        localStorage.setItem('appLanguage', selectedLang);

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
        const storedLang = localStorage.getItem('appLanguage');
        const cookieLang = getCookie('appLanguage');
        const browserLang = navigator.language.split('-')[0];

        let finalLang = 'en'; // default

        if (storedLang && ['en', 'uk'].includes(storedLang)) {
            finalLang = storedLang;
        } else if (cookieLang && ['en', 'uk'].includes(cookieLang)) {
            finalLang = cookieLang;
            // Sync localStorage with cookie
            localStorage.setItem('appLanguage', finalLang);
        } else if (urlLang && ['en', 'uk'].includes(urlLang)) {
            finalLang = urlLang;
        } else if (['en', 'uk'].includes(browserLang)) {
            finalLang = browserLang;
        }

        // Set document language
        document.documentElement.lang = finalLang;

        // Update language code span
        if (languageCodeSpan) {
            languageCodeSpan.textContent = finalLang.toUpperCase();
        }

        // Ensure cookie is set
        setCookie('appLanguage', finalLang);
    }

    // Initialize language on page load
    initializeLanguage();
});
