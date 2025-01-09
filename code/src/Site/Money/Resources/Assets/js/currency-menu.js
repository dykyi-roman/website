document.addEventListener('DOMContentLoaded', function() {
    const combinedDropdown = document.querySelector('.combined-dropdown');
    if (!combinedDropdown) return;

    const currencyCodeElement = combinedDropdown.querySelector('.currency-code');
    const currencyItems = combinedDropdown.querySelectorAll('[data-currency]');



    // Check if currency cookie exists, if not use data-currency
    const currentCurrency = getCookie('appCurrency');
    if (!currentCurrency && currencyItems.length > 0) {
        // Get the first item with data-currency or find an item with 'active' class
        const defaultCurrencyItem = 
            Array.from(currencyItems).find(item => item.classList.contains('active')) || 
            currencyItems[0];
        
        const defaultCurrencyCode = defaultCurrencyItem.dataset.currency;
        
        if (defaultCurrencyCode) {
            // Set cookie with 1 year expiration
            const expirationDate = new Date();
            expirationDate.setFullYear(expirationDate.getFullYear() + 1);
            document.cookie = `appCurrency=${defaultCurrencyCode}; expires=${expirationDate.toUTCString()}; path=/`;

            // Update displayed currency code
            if (currencyCodeElement) {
                currencyCodeElement.textContent = defaultCurrencyCode.toUpperCase();
            }

            // Update active state
            currencyItems.forEach(item => item.classList.remove('active'));
            defaultCurrencyItem.classList.add('active');
        }
    }

    // Handle currency selection
    currencyItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const currencyCode = this.dataset.currency;
            if (!currencyCode) return;

            // Set cookie with 1 year expiration
            const expirationDate = new Date();
            expirationDate.setFullYear(expirationDate.getFullYear() + 1);
            document.cookie = `appCurrency=${currencyCode}; expires=${expirationDate.toUTCString()}; path=/`;

            // Update displayed currency code
            if (currencyCodeElement) {
                currencyCodeElement.textContent = currencyCode.toUpperCase();
            }

            // Update active state
            currencyItems.forEach(item => item.classList.remove('active'));
            this.classList.add('active');

            updateProfileSetting('GENERAL', 'currency', currencyCode).catch(error => console.error('Failed to update currency:', error));

            // Reload page to apply new currency
            window.location.reload();
        });
    });
});
