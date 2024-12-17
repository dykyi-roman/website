document.addEventListener('DOMContentLoaded', async function () {
    console.log('Reset page script loaded');
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const tokenInput = document.getElementById('token');

    console.log('Form elements:', {
        resetPasswordForm: !!resetPasswordForm,
        passwordInput: !!passwordInput,
        confirmPasswordInput: !!confirmPasswordInput,
        tokenInput: !!tokenInput
    });

    // Get token from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    // Set token value in hidden input if token exists
    if (token && tokenInput) {
        tokenInput.value = token;
    }

    // Validation rules
    const validationRules = {
        password: {
            validate: (value) => value.trim().length >= 8,
            message: t.password_too_short || 'Password must be at least 8 characters'
        },
        confirm_password: {
            validate: (value, originalValue) => value.trim() === originalValue.trim(),
            message: t.passwords_do_not_match || 'Passwords do not match'
        }
    };

    // Field validation function
    function validateField(field) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Determine which validation rule to use
        if (fieldName === 'password') {
            rule = validationRules.password;
        } else if (fieldName === 'confirm_password') {
            const passwordInput = document.getElementById('password');
            rule = {
                validate: () => {
                    const confirmValue = value.trim();
                    const passwordValue = passwordInput.value.trim();
                    return confirmValue === passwordValue && confirmValue.length >= 8;
                },
                message: validationRules.confirm_password.message
            };
        } else {
            return true;
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

        const inputs = form.querySelectorAll('input[type="password"]');
        inputs.forEach(input => {
            // Validate on input
            input.addEventListener('input', function () {
                validateField(this);
                // Cross-validate confirm password when password changes
                if (this.name === 'password') {
                    const confirmPasswordInput = document.getElementById('confirm_password');
                    validateField(confirmPasswordInput);
                }
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this);
                // Cross-validate confirm password when password changes
                if (this.name === 'password') {
                    const confirmPasswordInput = document.getElementById('confirm_password');
                    validateField(confirmPasswordInput);
                }
            });

            // Initial validation state
            if (input.value) {
                validateField(input);
            }
        });
    }

    // Form submission handler
    function submitResetPassword(form) {
        form.addEventListener('submit', function (e) {
            // Prevent both default form submission and browser validation
            e.preventDefault();
            e.stopPropagation();

            // Remove any browser-based validation attributes
            const inputs = form.querySelectorAll('input[type="password"]');
            inputs.forEach(input => {
                input.removeAttribute('required');
                input.removeAttribute('minlength');
            });

            // Validate all fields
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (isValid) {
                // Prepare form data
                const formData = new FormData(form);

                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showSuccessMessage(t.success_message);
                        // Redirect after a short delay
                        setTimeout(() => {
                            window.location.href = data.redirectUrl || '/login';
                        }, 2000);
                    } else {
                        // Show error message
                        showErrorMessage(data.message || t.error_message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage(t.error_message);
                });
            }
        });
    }

    // Function to show success messages
    function showSuccessMessage(message) {
        const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
        alertContainer.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    // Function to show error messages
    function showErrorMessage(message) {
        const alertContainer = document.getElementById('alertContainer') || createAlertContainer();
        alertContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    // Function to create alert container if it doesn't exist
    function createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'container mt-3';
        document.body.insertBefore(container, document.body.firstChild);
        return container;
    }

    // Initialize
    setupFormValidation(resetPasswordForm);
    submitResetPassword(resetPasswordForm);

    console.log('Reset password script initialization complete');
});
