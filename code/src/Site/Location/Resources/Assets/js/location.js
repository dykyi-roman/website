document.addEventListener('DOMContentLoaded', async function () {

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // City search functionality
    async function searchCities(input, countrySelect) {
        const minLength = 3;
        const searchInterval = 2; // Search after every 2 characters
        let lastLength = 0;
        let citiesDropdown = null;

        input.addEventListener('input', debounce(async (e) => {
            const cityValue = e.target.value.trim();
            const countryCode = countrySelect.value;
            const lang = CookieService.get('locale') || 'en';

            // Create or get dropdown element
            if (!citiesDropdown) {
                citiesDropdown = document.createElement('ul');
                citiesDropdown.className = 'cities-dropdown';
                e.target.parentNode.appendChild(citiesDropdown);
            }

            // Hide dropdown if input is empty
            if (!cityValue) {
                citiesDropdown.style.display = 'none';
                lastLength = 0;
                return;
            }

            // Check if we should perform search
            const currentLength = cityValue.length;
            if (currentLength < minLength || 
                (currentLength > minLength && Math.abs(currentLength - lastLength) < searchInterval)) {
                return;
            }

            lastLength = currentLength;

            try {
                const response = await fetch(`/api/v1/location/cities?countryCode=${countryCode}&lang=${lang}&city=${encodeURIComponent(cityValue)}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();

                // Clear previous results
                citiesDropdown.innerHTML = '';
                if (data.cities && data.cities.length > 0) {
                    citiesDropdown.style.display = 'block';
                    data.cities.forEach(city => {
                        const li = document.createElement('li');
                        li.textContent = city.name;
                        li.addEventListener('click', () => {
                            input.value = city.name;
                            citiesDropdown.style.display = 'none';
                        });
                        citiesDropdown.appendChild(li);
                    });
                } else {
                    citiesDropdown.style.display = 'none';
                }
            } catch (error) {
                console.error('Error fetching cities:', error);
                citiesDropdown.style.display = 'none';
            }
        }, 300));

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (citiesDropdown && !input.contains(e.target) && !citiesDropdown.contains(e.target)) {
                citiesDropdown.style.display = 'none';
            }
        });
    }

    // Export functions for global use
    window.searchCities = searchCities;
});
