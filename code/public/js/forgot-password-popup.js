document.addEventListener('DOMContentLoaded', function() {
    console.log('Forgot password popup script loaded');

    // DOM Elements
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const forgotPasswordEmail = document.getElementById('forgotPasswordEmail');

    // Ensure the form has necessary validation elements
    function ensureValidationElements() {
        if (!forgotPasswordEmail.nextElementSibling || 
            !forgotPasswordEmail.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedbackElement = document.createElement('div');
            feedbackElement.classList.add('invalid-feedback');
            forgotPasswordEmail.parentNode.insertBefore(feedbackElement, forgotPasswordEmail.nextSibling);
        }
    }

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    const validationRules = {
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: 'Please enter a valid email address'
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
                message: 'This field is required'
            };
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

    // Setup form validation
    function setupFormValidation(form) {
        if (!form) return;

        // Ensure validation elements are in place
        ensureValidationElements();

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

    // Setup validation for the form
    if (forgotPasswordForm) {
        forgotPasswordForm.classList.add('needs-validation');
        setupFormValidation(forgotPasswordForm);

        // Handle form submission
        forgotPasswordForm.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();

            const isValid = Array.from(this.querySelectorAll('input')).every(input => validateField(input));

            if (isValid) {
                submitForgotPassword(this);
            }
        });
    }

    // Form submission handler
    function submitForgotPassword(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Show success message
                const successMessage = document.getElementById('forgot-password-success');
                if (successMessage) {
                    successMessage.textContent = result.message;
                    successMessage.style.display = 'block';
                }
                // Optional: Clear form or hide modal
                form.reset();
            } else {
                // Show error message
                const errorMessage = document.getElementById('forgot-password-error');
                if (errorMessage) {
                    errorMessage.textContent = result.message;
                    errorMessage.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = document.getElementById('forgot-password-error');
            if (errorMessage) {
                errorMessage.textContent = 'An unexpected error occurred. Please try again.';
                errorMessage.style.display = 'block';
            }
        });
    }
});
