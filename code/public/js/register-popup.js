document.addEventListener('DOMContentLoaded', async function() {
    console.log('Register popup script loaded');

    // Function to load translations
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
        }
    }

    // Get current language or default to English
    const currentLang = document.documentElement.lang || 'en';
    const t = await loadTranslations(currentLang);

    // Mapping specific keys for register popup
    const registerTranslations = {
        error_email_required: t['error_email_required'] || 'Email is required',
        error_invalid_email: t['error_invalid_email'] || 'Please enter a valid email address',
        error_password_length: t['error_password_length'] || 'Password must be at least 8 characters long',
        error_passwords_match: t['error_passwords_match'] || 'Passwords must match',
        error_network: t['error_network'] || 'Network error. Please try again.',
        label_email: t['label_email'] || 'Email Address',
        label_password: t['label_password'] || 'Password',
        label_confirm_password: t['label_confirm_password'] || 'Confirm Password'
    };

    // Update labels
    const emailLabel = document.querySelector('label[for="registerEmail"]');
    const passwordLabel = document.querySelector('label[for="registerPassword"]');
    const confirmPasswordLabel = document.querySelector('label[for="registerConfirmPassword"]');

    if (emailLabel) emailLabel.textContent = registerTranslations.label_email;
    if (passwordLabel) passwordLabel.textContent = registerTranslations.label_password;
    if (confirmPasswordLabel) confirmPasswordLabel.textContent = registerTranslations.label_confirm_password;

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const clientForm = document.getElementById('clientRegistrationForm');
    const partnerForm = document.getElementById('partnerRegistrationForm');

    // Initialize Bootstrap modal
    const registerModal = new bootstrap.Modal(popup, {
        backdrop: 'static',
        keyboard: false
    });

    // Check if forms exist before proceeding
    if (!clientForm || !partnerForm) {
        console.error('Registration forms not found!');
        return;
    }

    // Validation rules
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
            message: registerTranslations.error_invalid_email
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
    function validateField(field, form) {
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
                message: registerTranslations.error_email_required
            };
        }

        const isValid = rule.validate(value);
        const feedback = field.nextElementSibling;

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback') && field.type !== 'checkbox') {
                feedback.textContent = rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
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
                validateField(this, form);
            });

            // Validate on blur
            input.addEventListener('blur', function() {
                validateField(this, form);
            });
        });
    }

    // Setup validation for both forms
    setupFormValidation(clientForm);
    setupFormValidation(partnerForm);

    // Form submission handler
    async function submitRegistration(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!validateField(input, form)) {
                isValid = false;
            }
        });

        if (isValid) {
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
                registerModal.hide();
                alert('Registration successful!');
            } else {
                const emailInput = form.querySelector('input[type="email"]');
                if (emailInput) {
                    emailInput.setCustomValidity(data.message || registerTranslations.error_network);
                    emailInput.reportValidity();
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput) {
                emailInput.setCustomValidity(registerTranslations.error_network);
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

    // Event listener for register button
    const registerButtons = document.querySelectorAll('[data-action="register"]');
    registerButtons.forEach(button => {
        button.addEventListener('click', showRegistrationModal);
    });

    // Add event delegation for favorite buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-favorite')) {
            console.log('Favorite button clicked');
            showRegistrationModal();
        }
    });

    // Registration type selection
    const registerTypeButtons = document.querySelectorAll('.register-type-btn');
    registerTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            showRegistrationForm(type);
        });
    });

    // Switch to login popup
    const switchToLoginLink = document.getElementById('switch-to-login');
    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hide registration modal
            registerModal.hide();
            
            // Show login modal
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                const loginModalInstance = new bootstrap.Modal(loginModal);
                loginModalInstance.show();
            }
        });
    }

    // Functions
    function showRegistrationModal() {
        console.log('Showing registration modal');
        resetForms();
        showRegistrationTypeSelection();
        registerModal.show();
    }

    function showRegistrationTypeSelection() {
        registrationTypeSelection.classList.remove('d-none');
        clientForm.classList.add('d-none');
        partnerForm.classList.add('d-none');
    }

    function showRegistrationForm(type) {
        registrationTypeSelection.classList.add('d-none');
        
        if (type === 'client') {
            clientForm.classList.remove('d-none');
            partnerForm.classList.add('d-none');
        } else if (type === 'partner') {
            partnerForm.classList.remove('d-none');
            clientForm.classList.add('d-none');
        }
    }

    function resetForms() {
        [clientForm, partnerForm].forEach(form => {
            form.reset();
            form.classList.remove('was-validated');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
                input.setCustomValidity('');
            });
        });
    }

    // Modal events
    popup.addEventListener('hidden.bs.modal', function () {
        resetForms();
        showRegistrationTypeSelection();
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

    // Initialize social login buttons
    function initSocialLogin() {
        const googleLoginBtns = document.querySelectorAll('.btn-danger');
        const facebookLoginBtns = document.querySelectorAll('.btn-primary');

        googleLoginBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                console.log('Google Login clicked');
            });
        });

        facebookLoginBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                console.log('Facebook Login clicked');
            });
        });
    }

    // Initialize social buttons
    initSocialLogin();
});
