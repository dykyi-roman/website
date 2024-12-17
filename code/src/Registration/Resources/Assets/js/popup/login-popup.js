document.addEventListener('DOMContentLoaded', async function () {
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
        let rule;

        // Skip validation for remember_me field
        if (fieldName === 'remember_me') {
            return true;
        }

        // Determine which validation rule to use
        if (field.type === 'email') {
            rule = validationRules.email;
        } else if (field.type === 'password') {
            rule = validationRules.password;
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
            input.addEventListener('input', function () {
                validateField(this);
            });

            // Validate on blur
            input.addEventListener('blur', function () {
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

    // Form submission handler
    async function submitLogin(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input');

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            try {
                // Show spinner
                showModalSpinner(loginModal);

                const formData = new FormData(form);
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });

                const result = await response.json();
                if (!response.ok) {
                    hideModalSpinner(loginModal);

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

                    throw new Error(result.errors.message || t.error_invalid_credentials);
                }

                if (result.success) {
                    hideModalSpinner(loginForm);

                    window.location.href = result.redirectUrl || '/';
                } else {
                    hideModalSpinner(loginModal);

                    // Handle specific error messages
                    if (result.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.setCustomValidity(data.errors[field]);
                                input.reportValidity();
                            }
                        });
                    } else {
                        showErrorMessage(result.message || t.error_login_failed);
                    }
                }
            } catch (error) {
                hideModalSpinner(loginModal);
                showErrorMessage(error.message || t.error_network);
            }
        }
    }

    // Function to show error messages
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

    // Function to clear form errors and alerts
    function clearErrors(form) {
        const inputs = form.querySelectorAll('input');
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
            alert.textContent = '';
            alert.style.display = 'none';
        });
    }

    // Event listener for login buttons
    const loginButtons = document.querySelectorAll('[data-action="login"]');
    loginButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(loginModal);
            modal.show();

            // Clear any previous errors
            clearErrors(loginForm);

            // Ensure Remember Me checkbox is checked
            const rememberMeCheckbox = document.getElementById('rememberMe');
            if (rememberMeCheckbox) {
                rememberMeCheckbox.checked = true;
            }

            const inputs = loginModal.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid', 'is-valid');
            });
        });
    });

    // Modal events
    loginModal.addEventListener('hidden.bs.modal', function () {
        loginForm.reset();
        clearErrors(loginForm);
    });

    // Form submission event
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            submitLogin(this);
        });
    }
});
