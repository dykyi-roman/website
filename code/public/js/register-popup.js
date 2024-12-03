document.addEventListener('DOMContentLoaded', function() {
    console.log('Register popup script loaded');

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const closeBtn = document.getElementById('close-register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const registrationForm = document.getElementById('registrationForm');
    const registrationType = document.getElementById('registrationType');

    // Registration type buttons
    const registerTypeButtons = document.querySelectorAll('.register-type-btn');
    const registerFormSections = document.querySelectorAll('.register-form-section');
    const backButtons = document.querySelectorAll('.btn-back');

    // Event listener for register button
    const registerButtons = document.querySelectorAll('[data-action="register"]');
    registerButtons.forEach(button => {
        button.addEventListener('click', showPopup);
    });

    // Close button event
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
    }

    // Close on outside click
    if (popup) {
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                hidePopup();
            }
        });
    }

    // Registration type selection
    registerTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            showRegistrationForm(type);
        });
    });

    // Back buttons
    backButtons.forEach(button => {
        button.addEventListener('click', showRegistrationTypeSelection);
    });

    // City-Country relationship data
    const cityByCountry = {
        'us': ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Miami'],
        'ca': ['Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa'],
        'uk': ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Edinburgh']
    };

    // Update city options based on selected country
    function updateCityOptions(countrySelect, citySelect) {
        const country = countrySelect.value;
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (country && cityByCountry[country]) {
            cityByCountry[country].forEach(city => {
                const option = document.createElement('option');
                option.value = city.toLowerCase().replace(/\s+/g, '-');
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
    }

    // Add event listeners for country selects
    const clientCountrySelect = document.getElementById('client-country');
    const clientCitySelect = document.getElementById('client-city');
    const partnerCountrySelect = document.getElementById('partner-country');
    const partnerCitySelect = document.getElementById('partner-city');

    if (clientCountrySelect && clientCitySelect) {
        clientCountrySelect.addEventListener('change', () => {
            updateCityOptions(clientCountrySelect, clientCitySelect);
        });
    }

    if (partnerCountrySelect && partnerCitySelect) {
        partnerCountrySelect.addEventListener('change', () => {
            updateCityOptions(partnerCountrySelect, partnerCitySelect);
        });
    }

    // Form submission
    registrationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (validateForm(this)) {
            await submitRegistration(this);
        }
    });

    // Functions
    function showPopup() {
        console.log('Showing popup');
        if (popup) {
            popup.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Reset to initial state
            showRegistrationTypeSelection();
        } else {
            console.error('Popup element not found!');
        }
    }

    function hidePopup() {
        console.log('Hiding popup');
        if (popup) {
            popup.classList.remove('show');
            document.body.style.overflow = '';
            resetForm();
        }
    }

    function showRegistrationTypeSelection() {
        registrationTypeSelection.style.display = 'flex';
        registerFormSections.forEach(section => {
            section.style.display = 'none';
        });
        registrationType.value = '';
    }

    function showRegistrationForm(type) {
        // Hide registration type selection
        registrationTypeSelection.classList.remove('active');
        
        // Reset form
        resetForm();
        
        // Set hidden input value
        registrationType.value = type;
        
        // Show appropriate registration form section
        registerFormSections.forEach(section => {
            section.classList.remove('active');
            if (section.id === `${type}-register-section`) {
                section.classList.add('active');
            }
        });
    }

    function resetForm() {
        registrationForm.reset();
        showRegistrationTypeSelection();
        clearAllErrors();
    }

    function clearAllErrors() {
        const errorMessages = registrationForm.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(error => {
            error.textContent = '';
            error.style.display = 'none';
        });
    }

    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                showError(input, 'This field is required');
                isValid = false;
            } else {
                clearError(input);
            }
        });

        return isValid;
    }

    function showError(input, message) {
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
            feedback.style.display = 'block';
            input.classList.add('is-invalid');
        }
    }

    function clearError(input) {
        const feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.style.display = 'none';
            input.classList.remove('is-invalid');
        }
    }

    async function submitRegistration(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch('/api/register', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                console.log('Registration successful');
                hidePopup();
                // TODO: Show success message or redirect
            } else {
                console.log('Registration failed');
                const data = await response.json();
                // Handle validation errors from server
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, message]) => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            showError(input, message);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
        }
    }
});
