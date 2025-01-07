document.addEventListener('DOMContentLoaded', async function() {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const loginModal = document.getElementById('loginModal'); // Added login modal reference

    // Update email label
    const emailLabel = document.querySelector('label[for="forgotPasswordEmail"]');
    if (emailLabel) {
        emailLabel.textContent = t.label_email;
    }

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    const validationRules = {
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.error_invalid_email
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
                message: t.error_email_required
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

    // Function to show error messages
    function showErrorMessage(message) {
        // Find or create error message container
        let errorContainer = document.querySelector('.alert-danger');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'alert alert-danger mt-3';
            if (forgotPasswordForm) {
                forgotPasswordForm.insertBefore(errorContainer, forgotPasswordForm.firstChild);
            }
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
        let successContainer = document.querySelector('.alert-success');
        if (!successContainer) {
            successContainer = document.createElement('div');
            successContainer.className = 'alert alert-success mt-3';
            if (forgotPasswordForm) {
                forgotPasswordForm.insertBefore(successContainer, forgotPasswordForm.firstChild);
            }
        }
        successContainer.textContent = message;
        successContainer.style.display = 'block';

        // Automatically hide after 3 seconds
        setTimeout(() => {
            successContainer.style.display = 'none';
        }, 3000);
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

        const modal = form.closest('.modal-content');

        try {
            // Show spinner
            showModalSpinner(modal);

            const formData = new FormData(form);
            const response = await fetch('/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData.entries()))
            });

            const result = await response.json();
            if (!response.ok) {
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

                const errorMessage = result.errors && result.errors.message 
                    ? result.errors.message 
                    : t.error_generic_message;

                throw new Error(errorMessage);
            }

            // Hide spinner before showing any messages
            hideModalSpinner(modal);

            // Handle success
            if (result.success) {
                // Show success message
                const successMessage = result.message || t.success_reset_link;
                showSuccessMessage(successMessage);
            } else {
                // Show error message
                const errorMessage = result.message || t.error_generic_message;
                showErrorMessage(errorMessage);
            }
        } catch (error) {
            // Hide spinner on error
            hideModalSpinner(modal);

            console.error('Forgot Password Error:', error);
            showErrorMessage(error.message || t.error_network);
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

    // Modal events
    forgotPasswordModal.addEventListener('hidden.bs.modal', function () {
        forgotPasswordForm.reset();
        clearErrors(forgotPasswordForm);
    });

    // Initialize form
    setupForm();
    clearErrors(forgotPasswordForm);
});
