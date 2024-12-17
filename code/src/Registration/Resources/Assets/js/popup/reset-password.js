document.addEventListener('DOMContentLoaded', async function () {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    // Validation rules
    const validationRules = {
        password: {
            validate: (value) => value.trim().length >= 8,
            message: t.label_password || 'Password must be at least 8 characters long'
        },
        confirm_password: {
            validate: (value, originalValue) => value.trim() === originalValue.trim(),
            message: 'Passwords do not match'
        }
    };

    // Field validation function
    function validateField(field, originalValue = null) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Determine which validation rule to use
        if (fieldName === 'password') {
            rule = validationRules.password;
        } else if (fieldName === 'confirm_password') {
            rule = validationRules.confirm_password;
            originalValue = passwordInput.value;
        }

        const isValid = rule.validate(value, originalValue);
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
                    validateField(confirmPasswordInput);
                }
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this);
                // Cross-validate confirm password when password changes
                if (this.name === 'password') {
                    validateField(confirmPasswordInput);
                }
            });
        });
    }

    // Form submission handler
    function submitResetPassword(form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate all fields before submission
            const passwordValid = validateField(passwordInput);
            const confirmPasswordValid = validateField(confirmPasswordInput);

            if (passwordValid && confirmPasswordValid) {
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
                        // Redirect or show success message
                        window.location.href = data.redirectUrl || '/login';
                    } else {
                        // Show error message
                        showErrorMessage(data.message || 'Password reset failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('An unexpected error occurred');
                });
            }
        });
    }

    // Setup password toggle functionality
    function setupPasswordToggle() {
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            const toggleButton = input.nextElementSibling.nextElementSibling;
            
            toggleButton.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                this.classList.toggle('hide');
            });
        });
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
        document.querySelector('.content-wrapper').insertBefore(container, document.querySelector('.container'));
        return container;
    }

    // Initialize
    setupFormValidation(resetPasswordForm);
    submitResetPassword(resetPasswordForm);
    setupPasswordToggle();
});
