// Language menu selected
document.addEventListener('DOMContentLoaded', function() {
    console.log('Module::Locale::Created');

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

// Global translation loading utility
async function loadTranslations(lang) {
    const translationPath = (language) => `/translations/messages.${language}.json`;

    try {
        const response = await fetch(translationPath(lang));
        if (!response.ok) {
            console.warn(`Failed to load translations for language: ${lang}. Falling back to default.`);
            return await fetchFallbackTranslation();
        }
        return await response.json();
    } catch (error) {
        console.error('Error loading translations:', error);
        return await fetchFallbackTranslation();
    }
}

async function fetchFallbackTranslation() {
    try {
        const fallbackResponse = await fetch('/js/translations/messages.en.json');
        if (!fallbackResponse.ok) {
            throw new Error('Failed to load fallback translations.');
        }
        return await fallbackResponse.json();
    } catch (error) {
        console.error('Critical error loading fallback translations:', error);
        return {};
    }
}

if (typeof window !== 'undefined') {
    window.loadTranslations = loadTranslations;
}
