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
            validate: (value) => phoneRegex.test(value.trim()),
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

    // Field validation function
    function validateField(field, form) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Determine which validation rule to use
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
            // Default validation for required fields
            rule = {
                validate: (value) => ({isValid: value.trim() !== '', message: t.field_required}),
                message: t.field_required
            };
        }

        const validationResult = rule.validate(value);
        const isValid = typeof validationResult === 'boolean' ? validationResult : validationResult.isValid;
        const message = typeof validationResult === 'boolean' ? rule.message : validationResult.message;

        const feedback = field.nextElementSibling;

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

        return isValid;
    }

    // Add validation to form fields
    function setupFormValidation(form) {
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
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

            setTimeout(() => {
                window.location.href = '/';
            }, 1000);

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
        // Clear general error message
        const errorContainer = form.querySelector('.register-error-message');
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }

        // Clear field-specific errors
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
            const feedback = field.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.style.display = 'none';
            }
        });
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
