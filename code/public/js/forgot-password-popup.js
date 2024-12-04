document.addEventListener('DOMContentLoaded', function() {
    console.log('Forgot password popup script loaded');

    // DOM Elements
    const forgotPasswordModal = document.getElementById('forgotPasswordModal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const forgotPasswordEmail = document.getElementById('forgotPasswordEmail');

    // Email validation
    function validateEmail(email) {
        // Basic structure validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return { 
                valid: false, 
                message: 'Please enter a valid email address' 
            };
        }

        // Additional checks
        const [localPart, domain] = email.split('@');

        // Check local part length
        if (localPart.length < 1 || localPart.length > 64) {
            return { 
                valid: false, 
                message: 'Email local part must be between 1 and 64 characters' 
            };
        }

        // Check domain length
        if (domain.length < 3 || domain.length > 255) {
            return { 
                valid: false, 
                message: 'Email domain is invalid' 
            };
        }

        // Prevent certain special characters in local part
        const specialCharsRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+$/;
        if (!specialCharsRegex.test(localPart)) {
            return { 
                valid: false, 
                message: 'Email contains invalid characters' 
            };
        }

        // Prevent consecutive dots
        if (/\.{2,}/.test(localPart) || /\.{2,}/.test(domain)) {
            return { 
                valid: false, 
                message: 'Email cannot contain consecutive dots' 
            };
        }

        // Prevent starting or ending with a dot
        if (localPart.startsWith('.') || localPart.endsWith('.') ||
            domain.startsWith('.') || domain.endsWith('.')) {
            return { 
                valid: false, 
                message: 'Email cannot start or end with a dot' 
            };
        }

        return { valid: true };
    }

    // Handle form submission
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = forgotPasswordEmail.value.trim();

            // Clear previous errors
            clearError(forgotPasswordEmail);

            // Validate email
            const validationResult = validateEmail(email);
            if (!validationResult.valid) {
                showError(forgotPasswordEmail, validationResult.message);
                return;
            }

            submitForgotPassword(forgotPasswordForm);
        });

        // Add real-time validation on blur
        forgotPasswordEmail.addEventListener('blur', function() {
            const email = this.value.trim();
            
            // Clear previous errors
            clearError(this);

            // Validate email if not empty
            if (email) {
                const validationResult = validateEmail(email);
                if (!validationResult.valid) {
                    showError(this, validationResult.message);
                }
            }
        });
    }

    function showError(input, message) {
        clearError(input);
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }

    function clearError(input) {
        input.classList.remove('is-invalid');
        const errorElement = input.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    function submitForgotPassword(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('/forgot-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(forgotPasswordModal);
                modal.hide();
                alert('Password reset link sent to your email.');
            } else {
                showError(forgotPasswordEmail, data.message || 'Failed to send reset link');
            }
        })
        .catch(error => {
            console.error('Forgot password error:', error);
            showError(forgotPasswordEmail, 'An error occurred. Please try again.');
        });
    }
});
