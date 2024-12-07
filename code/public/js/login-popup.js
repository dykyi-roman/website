document.addEventListener('DOMContentLoaded', async function() {
    console.log('Login popup script loaded');

    // Function to load translations
    async function loadTranslations(lang) {
        try {
            const response = await fetch(`/translations/js/login-popup.${lang}.json`);
            if (!response.ok) {
                // Fallback to English if translation not found
                const fallbackResponse = await fetch('/translations/js/login-popup.en.json');
                return await fallbackResponse.json();
            }
            return await response.json();
        } catch (error) {
            console.error('Translation loading error:', error);
            // Fallback to hardcoded English translations
            return {
                placeholder_email: 'Please enter a valid email address',
                label_password: 'Password must be at least 8 characters long',
                error_invalid_credentials: 'Invalid email or password',
                error_network: 'An error occurred. Please try again.'
            };
        }
    }

    // Get current language or default to English
    const currentLang = document.documentElement.lang || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

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
        let isValid = true;
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    body: new FormData(form)
                });

                if (!response.ok) {
                    throw new Error(t.error_invalid_credentials);
                }
                // Successful login logic
                const data = await response.json();

                if (data.success) {
                    // Handle successful login
                    const modal = new bootstrap.Modal(loginModal);
                    modal.hide();

                    // Redirect or show success message
                    window.location.href = data.redirectUrl || '/dashboard';
                } else {
                    // Handle login errors
                    const emailInput = form.querySelector('input[type="email"]');
                    if (emailInput) {
                        emailInput.setCustomValidity(data.message || 'Login failed');
                        emailInput.reportValidity();
                    }
                }
            } catch (error) {
                console.error(error);
                showErrorMessage(t.error_network);
            }
        }
    }

    // Submit form data
    async function submitForm(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch('/api/login', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Handle successful login
                const modal = new bootstrap.Modal(loginModal);
                modal.hide();

                // Redirect or show success message
                window.location.href = data.redirectUrl || '/dashboard';
            } else {
                // Handle login errors
                const emailInput = form.querySelector('input[type="email"]');
                if (emailInput) {
                    emailInput.setCustomValidity(data.message || 'Login failed');
                    emailInput.reportValidity();
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            // Handle network or other errors
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput) {
                emailInput.setCustomValidity('An error occurred. Please try again.');
                emailInput.reportValidity();
            }
        }
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

    // Handle forgot password form submission
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('forgotPasswordEmail').value;

            fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Password reset link sent to your email.');
                    const modal = new bootstrap.Modal(loginModal);
                    modal.hide();
                } else {
                    alert(data.message || 'Failed to send reset link. Please try again.');
                }
            })
            .catch(error => {
                console.error('Forgot password error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }

    function showErrorMessage(message) {
        const errorElement = document.getElementById('error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('alert', 'alert-danger');
        }
    }
});
