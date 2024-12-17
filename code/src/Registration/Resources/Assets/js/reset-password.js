document.addEventListener('DOMContentLoaded', async function () {
    console.log('Reset password script loaded');
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const tokenInput = document.getElementById('token');

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
            message: t.password_too_short
        },
        confirm_password: {
            validate: (value) => value.trim() === passwordInput.value.trim(),
            message: t.passwords_do_not_match
        }
    };

    // Field validation function
    function validateField(field) {
        const value = field.value;
        const fieldName = field.name;
        let rule = validationRules[fieldName];

        if (!rule) {
            // Default validation for required fields
            rule = {
                validate: (value) => value.trim() !== '',
                message: t.this_field_is_required
            };
        }

        const isValid = rule.validate(value);
        const feedback = field.closest('.form-group').querySelector('.invalid-feedback');

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback) {
                feedback.style.color = '#dc3545';
                feedback.textContent = rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback) {
                feedback.style.color = 'transparent';
                feedback.textContent = rule.message;
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
                // If confirm password is being typed, also validate password
                if (this.name === 'confirm_password') {
                    validateField(passwordInput);
                }
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this);
                // If confirm password is being typed, also validate password
                if (this.name === 'confirm_password') {
                    validateField(passwordInput);
                }
            });

            // Initial validation state
            if (input.value) {
                validateField(input);
            }
        });
    }

    // Setup validation for reset password form
    setupFormValidation(resetPasswordForm);

    // Form submission handler
    async function submitResetPassword(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[type="password"]');

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            try {
                // Show spinner
                showModalSpinner(resetPasswordForm.closest('.modal'));

                const formData = new FormData(form);
                const response = await fetch('/reset-password', {
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
                    hideModalSpinner(resetPasswordForm.closest('.modal'));

                    // Handle specific field errors
                    if (result.errors && result.errors.field) {
                        const field = form.querySelector(`[name="${result.errors.field}"]`);
                        const fieldFeedback = field.closest('.form-group').querySelector('.invalid-feedback');

                        field.classList.add('is-invalid');
                        if (fieldFeedback) {
                            fieldFeedback.style.color = '#dc3545';
                            fieldFeedback.textContent = result.errors.message;
                        }

                        return false;
                    }

                    throw new Error(result.errors.message || t.error_reset_password);
                }

                if (result.success) {
                    hideModalSpinner(resetPasswordForm.closest('.modal'));
                } else {
                    hideModalSpinner(resetPasswordForm.closest('.modal'));

                    // Handle specific error messages
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.setCustomValidity(result.errors[field]);
                                input.reportValidity();
                            }
                        });
                    } else {
                        showErrorMessage(result.message || t.error_reset_password);
                    }
                }
            } catch (error) {
                hideModalSpinner(resetPasswordForm.closest('.modal'));
                showErrorMessage(error.message || t.error_network);
            }
        }
    }

    // Function to show error messages
    function showErrorMessage(message) {
        // Find or create error message container
        let errorContainer = document.querySelector('.reset-password-error-message');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-danger reset-password-error-message';
            resetPasswordForm.insertBefore(errorContainer, resetPasswordForm.firstChild);
        }
        errorContainer.textContent = message;
    }

    // Event listener for reset password form submission
    resetPasswordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitResetPassword(this);
    });
});
