document.addEventListener('DOMContentLoaded', function() {
    console.log('Register popup script loaded');

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const closeBtn = document.getElementById('close-register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const clientForm = document.getElementById('clientRegistrationForm');
    const partnerForm = document.getElementById('partnerRegistrationForm');
    const registrationType = document.getElementById('registrationType');

    // Check if forms exist before proceeding
    if (!clientForm || !partnerForm) {
        console.error('Registration forms not found!');
        return;
    }

    // Form validation setup
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[\d\s-()]{10,}$/;
    const nameRegex = /^[a-zA-Z\s'-]{2,50}$/;

    // Validation rules for different field types
    const validationRules = {
        name: {
            validate: (value) => nameRegex.test(value.trim()),
            message: 'Please enter a valid name (2-50 characters, letters only)'
        },
        partner_name: {
            validate: (value) => value.trim().length >= 2 && value.trim().length <= 100,
            message: 'Name must be between 2 and 100 characters'
        },
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: 'Please enter a valid email address'
        },
        tel: {
            validate: (value) => phoneRegex.test(value.trim()),
            message: 'Please enter a valid phone number'
        },
        select: {
            validate: (value) => value && value.trim() !== '',
            message: 'Please make a selection'
        }
    };

    // Field validation function
    function validateField(field) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Determine which validation rule to use
        if (fieldName === 'name') {
            rule = validationRules.name;
        } else if (fieldName === 'partner_name') {
            rule = validationRules.partner_name;
        } else if (field.type === 'email') {
            rule = validationRules.email;
        } else if (field.type === 'tel') {
            rule = validationRules.tel;
        } else if (field.tagName.toLowerCase() === 'select') {
            rule = validationRules.select;
        } else {
            // Default validation for required fields
            rule = {
                validate: (value) => value.trim() !== '',
                message: 'This field is required'
            };
        }

        const isValid = rule.validate(value);
        const feedback = field.nextElementSibling;

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback) {
                feedback.textContent = '';
            }
        }

        return isValid;
    }

    // Add validation to form fields
    function setupFormValidation(form) {
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            // Validate on input
            input.addEventListener('input', function() {
                validateField(this);
            });

            // Validate on blur
            input.addEventListener('blur', function() {
                validateField(this);
            });

            // Initial validation state
            if (input.value) {
                validateField(input);
            }
        });
    }

    // Setup validation for both forms
    setupFormValidation(clientForm);
    setupFormValidation(partnerForm);

    // Form submission handler
    function submitRegistration(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            // Form is valid, proceed with submission
            submitForm(form);
        }
    }

    // Submit form data
    async function submitForm(form) {
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

    // Event listeners for form submission
    clientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitRegistration(this);
    });

    partnerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitRegistration(this);
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

            const invalidInputs = popup.querySelectorAll('.is-invalid');
            invalidInputs.forEach(input => {
                input.classList.remove('is-invalid');
            });

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
    }

    function showRegistrationForm(type) {
        console.log('Showing registration form:', type);
        
        // Hide registration type selection
        registrationTypeSelection.style.display = 'none';

        // Hide both forms first
        clientForm.style.display = 'none';
        partnerForm.style.display = 'none';

        // Show appropriate form
        if (type === 'client') {
            clientForm.style.display = 'block';
            const sections = clientForm.querySelectorAll('.register-form-section');
            console.log('Client form sections:', sections.length);
            sections.forEach(section => {
                section.style.display = 'block';
                console.log('Section display:', section.style.display);
            });
        } else if (type === 'partner') {
            partnerForm.style.display = 'block';
            const sections = partnerForm.querySelectorAll('.register-form-section');
            console.log('Partner form sections:', sections.length);
            sections.forEach(section => {
                section.style.display = 'block';
                console.log('Section display:', section.style.display);
            });
        }
    }

    function resetForm() {
        clientForm.reset();
        clientForm.classList.remove('was-validated');
        partnerForm.reset();
        partnerForm.classList.remove('was-validated');
        showRegistrationTypeSelection();
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
