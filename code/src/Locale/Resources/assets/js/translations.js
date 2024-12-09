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

// Export for module systems or global use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { loadTranslations };
}
if (typeof window !== 'undefined') {
    window.loadTranslations = loadTranslations;
}
