document.addEventListener('DOMContentLoaded', async function() {
    console.log('Forgot password popup script loaded');

    // Get current language or default to English
    const currentLang = document.documentElement.lang || 'en';
    const t = await loadTranslations(currentLang);

    // Mapping specific keys for forgot password popup
    const forgotPasswordTranslations = {
        error_email_required: t['error_email_required'] || 'Email is required',
        error_invalid_email: t['error_invalid_email'] || 'Please enter a valid email address',
        error_network: t['error_network'] || 'Network error. Please try again.',
        success_reset_link: t['success_reset_link'] || 'Password reset link sent to your email',
        label_email: t['label_email'] || 'Email Address'
    };

    // DOM Elements
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const loginModal = document.getElementById('loginModal'); // Added login modal reference

    // Update email label
    const emailLabel = document.querySelector('label[for="forgotPasswordEmail"]');
    if (emailLabel) {
        emailLabel.textContent = forgotPasswordTranslations.label_email;
    }

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    const validationRules = {
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: forgotPasswordTranslations.error_invalid_email
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
        } else {
            // Default validation for required fields
            rule = {
                validate: (value) => value.trim() !== '',
                message: forgotPasswordTranslations.error_email_required
            };
        }

        const isValid = rule.validate(value);
        const feedback = field.nextElementSibling;

        // Always ensure feedback element exists
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            const feedbackElement = document.createElement('div');
            feedbackElement.classList.add('invalid-feedback');
            field.parentNode.insertBefore(feedbackElement, field.nextSibling);
        }

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            const feedbackElement = field.nextElementSibling;
            if (feedbackElement && feedbackElement.classList.contains('invalid-feedback') && field.type !== 'checkbox') {
                feedbackElement.textContent = rule.message;
                feedbackElement.style.display = 'block';
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            const feedbackElement = field.nextElementSibling;
            if (feedbackElement && field.type !== 'checkbox') {
                feedbackElement.textContent = '';
                feedbackElement.style.display = 'none';
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

    // Ensure validation elements are in place
    function ensureValidationElements() {
        const emailInput = forgotPasswordForm.querySelector('input[type="email"]');
        if (emailInput && (!emailInput.nextElementSibling || 
            !emailInput.nextElementSibling.classList.contains('invalid-feedback'))) {
            const feedbackElement = document.createElement('div');
            feedbackElement.classList.add('invalid-feedback');
            emailInput.parentNode.insertBefore(feedbackElement, emailInput.nextSibling);
        }
    }

    // Setup validation for the form
    function setupForm() {
        if (!forgotPasswordForm) return;

        // Ensure validation elements
        ensureValidationElements();

        // Setup validation
        setupFormValidation(forgotPasswordForm);

        // Form submission handler
        forgotPasswordForm.addEventListener('submit', function(event) {
            event.preventDefault();
            submitForgotPassword(this);
        });
    }

    // Submit form data
    async function submitForgotPassword(form) {
        // Validate all inputs first
        let isValid = true;
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        // If not valid, stop submission
        if (!isValid) return;

        try {
            const formData = new FormData(form);
            const response = await fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData.entries()))
            });

            const result = await response.json();

            // Handle success
            if (result.success) {
                // Show success message
                const successMessage = document.getElementById('forgot-password-success');
                if (successMessage) {
                    successMessage.textContent = forgotPasswordTranslations.success_reset_link;
                    successMessage.style.display = 'block';
                }
            } else {
                // Show error message
                const emailInput = form.querySelector('input[type="email"]');
                if (emailInput) {
                    emailInput.setCustomValidity(result.message || forgotPasswordTranslations.error_network);
                    emailInput.reportValidity();
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const emailInput = form.querySelector('input[type="email"]');
            if (emailInput) {
                emailInput.setCustomValidity(forgotPasswordTranslations.error_network);
                emailInput.reportValidity();
            }
        }
    }

    // Open forgot password popup
    const forgotPasswordButtons = document.querySelectorAll('[data-action="forgot-password"]');
    forgotPasswordButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Close login modal if it's open
            if (loginModal) {
                const loginModalInstance = bootstrap.Modal.getInstance(loginModal);
                if (loginModalInstance) {
                    loginModalInstance.hide();
                    
                    // Clear login form inputs and validation states
                    const loginInputs = loginModal.querySelectorAll('input');
                    loginInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('is-invalid', 'is-valid');
                        if (input.type === 'checkbox') {
                            input.checked = false;
                        }
                    });
                }
            }

            // Show forgot password modal
            const modal = new bootstrap.Modal(forgotPasswordModal);
            modal.show();

            // Clear forgot password form
            const inputs = forgotPasswordModal.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid', 'is-valid');
            });
        });
    });

    // Initialize form
    setupForm();
});
