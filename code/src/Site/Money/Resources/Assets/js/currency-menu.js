document.addEventListener('DOMContentLoaded', function() {
    const combinedDropdown = document.querySelector('.combined-dropdown');
    if (!combinedDropdown) return;

    const currencyCodeElement = combinedDropdown.querySelector('.currency-code');
    const currencyItems = combinedDropdown.querySelectorAll('[data-currency]');

    // Function to update currency UI
    function updateCurrencyUI(currencyCode) {
        if (!currencyCode) return;
        
        // Update displayed currency code
        if (currencyCodeElement) {
            currencyCodeElement.textContent = currencyCode.toUpperCase();
        }

        // Update active state
        currencyItems.forEach(item => {
            const itemCurrency = item.dataset.currency?.toUpperCase();
            item.classList.toggle('active', itemCurrency === currencyCode.toUpperCase());
        });
    }

    // Listen for currency changes from base.js
    document.addEventListener('currencyChanged', (event) => {
        updateCurrencyUI(event.detail.currency);
    });


    // Initialize with current currency from cookie
    const currentCurrency = window.getCookie('appCurrency');
    if (currentCurrency) {
        updateCurrencyUI(currentCurrency);
    } else if (currencyItems.length > 0) {
        // If no currency cookie exists, use default from active item or first item
        const defaultCurrencyItem = 
            Array.from(currencyItems).find(item => item.classList.contains('active')) || 
            currencyItems[0];
        
        const defaultCurrencyCode = defaultCurrencyItem.dataset.currency;
        if (defaultCurrencyCode) {
            updateCurrencyUI(defaultCurrencyCode);
            window.setCookie('appCurrency', defaultCurrencyCode.toUpperCase());
        }
    }

    // Handle currency selection
    currencyItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const currencyCode = this.dataset.currency;
            if (!currencyCode) return;

            const upperCurrencyCode = currencyCode.toUpperCase();
            window.setCookie('appCurrency', upperCurrencyCode);
            updateCurrencyUI(upperCurrencyCode);

            // Dispatch currency change event
            document.dispatchEvent(new CustomEvent('currencyChanged', { 
                detail: { currency: upperCurrencyCode } 
            }));

            updateProfileSetting('GENERAL', 'currency', currencyCode)
                .catch(error => console.error('Failed to update currency:', error));

            // Reload page to apply new currency
            window.location.reload();
        });
    });
});
