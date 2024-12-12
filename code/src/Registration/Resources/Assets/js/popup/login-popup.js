document.addEventListener('DOMContentLoaded', async function() {
    console.log('Login popup script loaded');

    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const validationRules = {
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.placeholder_email
        },
        password: {
            validate: (value) => value.trim().length >= 8,
            message: t.label_password
        }
    };

    // Field validation function
    function validateField(field) {
        const value = field.value;
        const fieldName = field.name;
        console.log(`Validating field: ${fieldName}, value: ${value}`);
        let rule;

        // Determine which validation rule to use
        if (field.type === 'email') {
            rule = validationRules.email;
            console.log('Applying email validation rule');
        } else if (field.type === 'password') {
            rule = validationRules.password;
            console.log('Applying password validation rule');
        } else {
            // Default validation for required fields
            rule = {
                validate: (value) => value.trim() !== '',
                message: 'This field is required'
            };
            console.log('Applying default validation rule');
        }

        const isValid = rule.validate(value);
        console.log(`Validation result for ${fieldName}: ${isValid}`);
        const feedback = field.nextElementSibling;

        if (!isValid) {
            console.log(`Validation failed for ${fieldName}: ${rule.message}`);
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback') && field.type !== 'checkbox') {
                feedback.textContent = rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback && field.type !== 'checkbox') {
                feedback.textContent = '';
            }
        }

        return isValid;
    }

    // Add validation to form fields
    function setupFormValidation(form) {
        if (!form) return;

        const inputs = form.querySelectorAll('input');
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

    // Setup validation for login form
    setupFormValidation(loginForm);

    // Form submission handler
    async function submitLogin(form) {
        console.log('Starting form submission validation');
        let isValid = true;
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            console.log(`Checking input: ${input.name}`);
            if (!validateField(input)) {
                console.log(`Validation failed for: ${input.name}`);
                isValid = false;
            }
        });

        if (isValid) {
            console.log('All validations passed, proceeding with form submission');
            console.log('eeee 33333');
            try {
                // Show spinner
                showModalSpinner(loginForm);

                const formData = new FormData(form);
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    hideModalSpinner(loginForm);

                    const data = await response.json();
                    throw new Error(data.message || t.error_invalid_credentials);
                }

                const data = await response.json();
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(loginModal);
                    if (modal) {
                        modal.hide();
                    }

                    hideModalSpinner(loginForm);

                    window.location.href = data.redirectUrl || '/';
                } else {
                    hideModalSpinner(loginForm);

                    // Handle specific error messages
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.setCustomValidity(data.errors[field]);
                                input.reportValidity();
                            }
                        });
                    } else {
                        showErrorMessage(data.message || t.error_login_failed);
                    }
                }
            } catch (error) {
                hideModalSpinner(loginForm);

                console.error('Login error:', error);
                showErrorMessage(error.message || t.error_network);
            }
        }
    }

    function showErrorMessage(message) {
        // Find or create error message container
        let errorContainer = document.querySelector('.login-error-message');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-danger login-error-message mt-3';
            loginForm.insertBefore(errorContainer, loginForm.firstChild);
        }
        errorContainer.textContent = message;
        errorContainer.style.display = 'block';
    }

    // Event listener for login buttons
    const loginButtons = document.querySelectorAll('[data-action="login"]');
    loginButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(loginModal);
            modal.show();

            const inputs = loginModal.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid', 'is-valid');
                
                // Uncheck the "Remember me" checkbox
                if (input.type === 'checkbox' && input.id === 'rememberMe') {
                    input.checked = false;
                }
            });
        });
    });

    // Form submission event
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            submitLogin(this);
        });
    }
});
