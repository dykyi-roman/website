document.addEventListener('DOMContentLoaded', function() {
    const combinedDropdown = document.querySelector('.combined-dropdown');
    if (!combinedDropdown) return;

    const languageCodeElement = combinedDropdown.querySelector('.language-code');
    const languageItems = combinedDropdown.querySelectorAll('[data-lang]');

    // Handle language selection
    languageItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const langCode = this.dataset.lang;
            if (!langCode) return;

            window.setCookie('locale', langCode);

            // Update displayed language code
            if (languageCodeElement) {
                languageCodeElement.textContent = langCode.toUpperCase();
            }

            // Update active state
            languageItems.forEach(item => item.classList.remove('active'));
            this.classList.add('active');

            updateProfileSetting('GENERAL', 'language', langCode).catch(error => console.error('Failed to update language:', error));

            // Update URL and reload page
            const url = new URL(window.location.href);
            url.searchParams.set('lang', langCode);
            window.location.href = url.toString();
        });
    });
});

// Global translation loading utility
window.loadTranslations = async function(lang) {
    try {
        const response = await fetch(`/translations/messages.${lang}.json`);
        if (!response.ok) {
            console.warn(`Failed to load translations for ${lang}, falling back to English`);
            const fallback = await fetch('/translations/messages.en.json');
            return fallback.ok ? await fallback.json() : {};
        }
        return await response.json();
    } catch (error) {
        console.error('Error loading translations:', error);
        return {};
    }
};
