document.addEventListener('DOMContentLoaded', async function () {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const tokenInput = document.getElementById('token');

    // Check if form exists (might not exist if token is invalid)
    if (!resetPasswordForm) {
        console.log('No reset password form - token might be invalid');
        return;
    }

    // Get token from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    // Set token value in hidden input if token exists
    if (token && tokenInput) {
        tokenInput.value = token;
    }

    // Password regex for complexity
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;

    // Validation rules
    const validationRules = {
        password: {
            validate: (value) => {
                const password = value.trim();
                if (password.length < 8) {
                    return {isValid: false, message: t.password_length_validation || 'Password must be at least 8 characters long'};
                }
                if (!passwordRegex.test(password)) {
                    return {isValid: false, message: t.password_complexity_validation || 'Password must include uppercase, lowercase, number, and special character'};
                }
                return {isValid: true};
            },
            message: t.password_validation
        },
        confirmPassword: {
            validate: (value) => {
                const password = passwordInput.value.trim();
                const confirmPassword = value.trim();
                if (confirmPassword !== password) {
                    return {isValid: false, message: t.passwords_do_not_match || 'Passwords do not match'};
                }
                return {isValid: true};
            },
            message: t.passwords_do_not_match || 'Passwords do not match'
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
                message: t.this_field_is_required || 'This field is required'
            };
        }

        const validationResult = rule.validate(value);
        const isValid = validationResult.isValid !== false;
        const feedback = field.closest('.form-group').querySelector('.invalid-feedback');

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback) {
                feedback.style.color = '#dc3545';
                feedback.textContent = validationResult.message || rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback) {
                feedback.style.color = 'transparent';
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
                // If confirm password is being typed, also validate password
                if (this.name === 'confirmPassword') {
                    validateField(passwordInput);
                }
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this);
                // If confirm password is being typed, also validate password
                if (this.name === 'confirmPassword') {
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
                showModalSpinner(form);

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
                    hideModalSpinner(form);

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

                    throw new Error(result.errors?.message || t.error_reset_password);
                }

                if (result.success) {
                    hideModalSpinner(form);
                    showSuccessMessage(t.success_reset_password);
                } else {
                    hideModalSpinner(form);

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
                hideModalSpinner(form.closest('.modal'));
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
        errorContainer.style.display = 'block';
        
        // Automatically hide the error message after 3 seconds
        setTimeout(() => {
            errorContainer.style.display = 'none';
        }, 3000);
    }

    // Function to show success messages
    function showSuccessMessage(message) {
        // Find or create success message container
        let successContainer = document.querySelector('.reset-password-success-message');
        if (!successContainer) {
            successContainer = document.createElement('div');
            successContainer.className = 'alert alert-success reset-password-success-message';
            resetPasswordForm.insertBefore(successContainer, resetPasswordForm.firstChild);
        }
        successContainer.textContent = message;

        // Hide all form groups
        const formGroups = resetPasswordForm.querySelectorAll('.form-group');
        formGroups.forEach(group => {
            group.style.display = 'none';
        });

        // Hide submit button
        const submitButton = resetPasswordForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.style.display = 'none';
        }

        // Optional: Disable form after successful message
        resetPasswordForm.querySelectorAll('input, button').forEach(el => {
            el.disabled = true;
        });
    }

    // Event listener for reset password form submission
    resetPasswordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitResetPassword(this);
    });
});
