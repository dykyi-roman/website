document.addEventListener('DOMContentLoaded', async function () {
    console.log('Register popup script loaded');

    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const clientForm = document.getElementById('clientRegistrationForm');
    const partnerForm = document.getElementById('partnerRegistrationForm');

    // Initialize city fields as disabled
    const clientCity = document.getElementById('client-city');
    const partnerCity = document.getElementById('partner-city');
    if (clientCity) clientCity.disabled = true;
    if (partnerCity) partnerCity.disabled = true;

    // Add event listeners for country selects
    const clientCountry = document.getElementById('client-country');
    const partnerCountry = document.getElementById('partner-country');

    if (clientCountry) {
        clientCountry.addEventListener('change', function() {
            if (clientCity) {
                clientCity.disabled = !this.value;
                if (this.value) {
                    clientCity.focus();
                }
            }
        });
    }

    if (partnerCountry) {
        partnerCountry.addEventListener('change', function() {
            if (partnerCity) {
                partnerCity.disabled = !this.value;
                if (this.value) {
                    partnerCity.focus();
                }
            }
        });
    }

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
    const phoneRegex = /^\+\d{10,15}$/; // + followed by 10-15 digits
    const nameRegex = /^[a-zA-Z\s'-]{2,100}$/;
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;

    // Validation rules for different field types
    const validationRules = {
        name: {
            validate: (value) => nameRegex.test(value.trim()),
            message: t.name_validation
        },
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.error_invalid_email
        },
        tel: {
            validate: (value) => {
                // Check if the phone number matches the regex (starts with +, 10-15 digits)
                const isValidFormat = phoneRegex.test(value.trim());
                
                // Additional length check
                const cleanedValue = value.replace(/\D/g, '');
                const isValidLength = cleanedValue.length >= 10 && cleanedValue.length <= 15;
                
                return isValidFormat && isValidLength;
            },
            message: t.phone_validation
        },
        select: {
            validate: (value) => value.trim() !== '',
            message: t.selection_validation
        },
        password: {
            validate: (value) => {
                const password = value.trim();
                if (password.length < 8) {
                    return {isValid: false, message: t.password_length_validation};
                }
                if (!passwordRegex.test(password)) {
                    return {isValid: false, message: t.password_complexity_validation};
                }
                return {isValid: true};
            },
            message: t.password_validation
        }
    };

    // Add phone input validation to enforce rules
    function setupPhoneInputValidation(input) {
        input.addEventListener('input', function(e) {
            // Remove any characters that are not + or digits
            let value = e.target.value.replace(/[^+\d]/g, '');
            
            // Ensure the first character is always +
            if (!value.startsWith('+')) {
                value = '+' + value.replace(/\+/g, '');
            }
            
            // Limit to first character being + and rest being digits
            e.target.value = value;
        });
    }

    // Field validation function
    function validateField(field, form) {
        const value = field.value;
        const feedback = field.nextElementSibling;
        
        // Determine validation rule and message
        const validationConfig = getValidationConfig(field, value);
        const isValid = validationConfig.isValid;
        const message = validationConfig.message || (feedback ? feedback.textContent : '');

        // Update field UI state
        updateFieldValidationState(field, feedback, isValid, message);

        return isValid;
    }

    // Get validation configuration based on field type
    function getValidationConfig(field, value) {
        const fieldName = field.name;

        // Special case for city field
        if (fieldName === 'cityName' && value.trim() !== '') {
            const transcriptionAttr = field.id === 'partner-city' 
                ? 'data-partner-city-transcription' 
                : 'data-client-city-transcription';
            
            const hasTranscription = field.getAttribute(transcriptionAttr) !== null;
            
            if (!hasTranscription) {
                return {
                    isValid: false,
                    message: t.city_select_from_list
                };
            }
            
            return {
                isValid: true,
                message: ''
            };
        }

        // Get validation rule based on field type
        let rule;
        if (field.type === 'email') {
            rule = validationRules.email;
        } else if (field.type === 'tel') {
            rule = validationRules.tel;
        } else if (field.type === 'password') {
            rule = validationRules.password;
        } else if (field.tagName.toLowerCase() === 'select') {
            rule = validationRules.select;
        } else if (fieldName === 'name') {
            rule = validationRules.name;
        } else {
            rule = {
                validate: (value) => ({isValid: value.trim() !== '', message: t.field_required}),
                message: t.field_required
            };
        }

        // Execute validation
        const validationResult = rule.validate(value);
        return {
            isValid: typeof validationResult === 'boolean' ? validationResult : validationResult.isValid,
            message: typeof validationResult === 'boolean' ? rule.message : validationResult.message
        };
    }

    // Update field UI based on validation state
    function updateFieldValidationState(field, feedback, isValid, message) {
        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        }
    }

    // Add validation to form fields
    function setupFormValidation(form) {
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            // Add special handling for phone inputs
            if (input.type === 'tel') {
                setupPhoneInputValidation(input);
            }

            // Validate on input
            input.addEventListener('input', function () {
                validateField(this, form);
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this, form);
            });
        });
    }

    // Setup validation for both forms
    setupFormValidation(clientForm);
    setupFormValidation(partnerForm);

    // Setup password toggle functionality
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        // Create wrapper div
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        
        // Move the input and its feedback to the wrapper
        const feedback = input.nextElementSibling;
        wrapper.appendChild(input);
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            wrapper.appendChild(feedback);
        }

        // Create toggle button
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'password-toggle hide';
        toggleButton.setAttribute('aria-label', 'Toggle password visibility');
        wrapper.appendChild(toggleButton);

        // Add click event
        toggleButton.addEventListener('click', function() {
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            toggleButton.classList.toggle('hide');
        });
    });

    // Add city search functionality
    if (partnerCity) {
        window.searchCities(partnerCity, partnerCountry);
    }

    if (clientCity) {
        window.searchCities(clientCity, clientCountry);
    }

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
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const modal = form.closest('.modal-content');

        try {
            // Show spinner
            showModalSpinner(modal);

            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const result = await response.json();

                // Hide spinner before showing error
                hideModalSpinner(modal);
                
                console.error('Registration error:', result);
                
                // Handle specific field errors
                if (result.errors && result.errors.field) {
                    const field = form.querySelector(`[name="${result.errors.field}"]`);
                    const fieldFeedback = field.nextElementSibling;
                    
                    field.classList.add('is-invalid');
                    if (fieldFeedback && fieldFeedback.classList.contains('invalid-feedback')) {
                        fieldFeedback.textContent = result.errors.message;
                    }
                    return false;
                }

                // Generic error handling
                const errorMessage = result.errors && result.errors.message 
                    ? result.errors.message 
                    : t.error_generic_message;
                
                throw new Error(errorMessage);
            }

            // Hide spinner on success
            hideModalSpinner(modal);

            // Redirect immediately
            window.location.href = '/';

            return true;
        } catch (error) {
            // Hide spinner on error
            hideModalSpinner(modal);
            
            console.error('Global Registration error:', error);
            showErrorMessage(error.message || t.error_network);

            return false;
        }
    }

    function clearErrors(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        });

        // Remove alert classes and hide alert elements
        const alertElements = form.querySelectorAll('.alert-danger, .alert-success');
        alertElements.forEach(alert => {
            alert.classList.remove('alert-danger', 'alert-success');
            alert.textContent = '';
            alert.style.display = 'none';
        });

        // Reset city fields to disabled state
        const clientCity = document.getElementById('client-city');
        const partnerCity = document.getElementById('partner-city');
        if (clientCity) clientCity.disabled = true;
        if (partnerCity) partnerCity.disabled = true;
    }

    // Function to show error messages
    function showErrorMessage(message) {
        // Find or create error message container
        let errorContainer = document.querySelector('.registration-error-message');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-danger registration-error-message mt-3';
            const form = document.querySelector('#clientRegistrationForm, #partnerRegistrationForm');
            if (form) {
                form.insertBefore(errorContainer, form.firstChild);
            }
        }
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    }

    // Event listeners for form submission
    clientForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitRegistration(this);
    });

    partnerForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitRegistration(this);
    });

    // Event listener for register button
    const registerButtons = document.querySelectorAll('[data-action="register"]');
    registerButtons.forEach(button => {
        button.addEventListener('click', showRegistrationModal);
    });

    // Add event delegation for favorite buttons
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-favorite')) {
            console.log('Favorite button clicked');
            showRegistrationModal();
        }
    });

    // Registration type selection
    const registerTypeButtons = document.querySelectorAll('.register-type-btn');
    registerTypeButtons.forEach(button => {
        button.addEventListener('click', function () {
            const type = this.dataset.type;
            showRegistrationForm(type);
        });
    });

    // Switch to login popup
    const switchToLoginLink = document.getElementById('switch-to-login');
    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', function (e) {
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
            clearErrors(form);
        });
    }

    // Modal events
    popup.addEventListener('hidden.bs.modal', function () {
        resetForms();
        showRegistrationTypeSelection();
    });

    // Initialize social login buttons
    function initSocialLogin() {
        const googleLoginBtns = document.querySelectorAll('.social-btn-google');
        const facebookLoginBtns = document.querySelectorAll('.social-btn-facebook');

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
