document.addEventListener('DOMContentLoaded', function() {
    console.log('Register popup script loaded');

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const closeBtn = document.getElementById('close-register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const registrationForm = document.getElementById('registrationForm');
    const registrationType = document.getElementById('registrationType');

    // Form validation setup
    const form = registrationForm;
    const inputs = form.querySelectorAll('input, select');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[\d\s-()]{10,}$/;

    // Validation functions
    const validators = {
        email: (value) => emailRegex.test(value.trim()) ? '' : 'Please enter a valid email address',
        tel: (value) => phoneRegex.test(value.trim()) ? '' : 'Please enter a valid phone number',
        text: (value) => value.trim() ? '' : 'This field is required',
        select: (value) => value ? '' : 'Please make a selection'
    };

    // Add validation to all form fields
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateField(this);
        });

        input.addEventListener('blur', function() {
            validateField(this);
        });
    });

    // Field validation function
    function validateField(field) {
        const value = field.value;
        let error = '';

        // Get the appropriate validator
        const validator = validators[field.type] || validators.text;
        if (field.tagName.toLowerCase() === 'select') {
            error = validators.select(value);
        } else {
            error = validator(value);
        }

        // Update field status
        if (error) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            const feedback = field.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = error;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }

        return !error;
    }

    // Form submission handler
    registrationForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let isValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            // Form is valid, proceed with submission
            await submitRegistration(this);
        }
    });

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
        registrationTypeSelection.style.display = 'none';
        
        // Reset form
        registrationForm.reset();
        registrationForm.classList.remove('was-validated');
        
        // Set hidden input value
        registrationType.value = type;
        
        // Show appropriate registration form section
        registerFormSections.forEach(section => {
            if (section.id === `${type}-register-section`) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    function resetForm() {
        registrationForm.reset();
        registrationForm.classList.remove('was-validated');
        showRegistrationTypeSelection();
    }

    async function submitRegistration(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch('/api/register', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Handle successful registration
                hidePopup();
                // You might want to show a success message or redirect
                alert('Registration successful!');
            } else {
                // Handle registration errors
                // Set custom validity for server-side errors
                const emailInput = form.querySelector('input[type="email"]');
                if (emailInput) {
                    emailInput.setCustomValidity(data.message || 'Registration failed');
                    emailInput.reportValidity();
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            // Handle network or other errors
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput) {
                emailInput.setCustomValidity('An error occurred. Please try again.');
                emailInput.reportValidity();
            }
        }
    }

    // Switch to login popup
    const switchToLoginLink = document.getElementById('switch-to-login');
    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hide register popup
            const registerPopup = document.getElementById('register-popup');
            if (registerPopup) {
                registerPopup.style.display = 'none';
            }

            // Show login modal
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                const modal = new bootstrap.Modal(loginModal);
                modal.show();
            }
        });
    }

    // Добавляем функции для социальной авторизации
    function initSocialLogin() {
        const googleLoginBtn = document.getElementById('google-login-btn');
        const facebookLoginBtn = document.getElementById('facebook-login-btn');

        if (googleLoginBtn) {
            googleLoginBtn.addEventListener('click', () => {
                // TODO: Реализовать логику Google OAuth
                console.log('Google Login clicked');
            });
        }

        if (facebookLoginBtn) {
            facebookLoginBtn.addEventListener('click', () => {
                // TODO: Реализовать логику Facebook OAuth
                console.log('Facebook Login clicked');
            });
        }
    }

    // Добавляем инициализацию социальных кнопок
    initSocialLogin();
});
