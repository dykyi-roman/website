// Global translation loading utility
async function loadTranslations(lang) {
    try {
        const response = await fetch(`/translations/messages.${lang}.json`);
        if (!response.ok) {
            const fallbackResponse = await fetch('/translations/messages.en.json');
            return await fallbackResponse.json();
        }
        return await response.json();
    } catch (error) {
        console.error('Translation loading error:', error);
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
