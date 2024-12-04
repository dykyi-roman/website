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
            return false;
        }

        // Additional checks
        const [localPart, domain] = email.split('@');

        // Check local part length
        if (localPart.length < 1 || localPart.length > 64) {
            return false;
        }

        // Check domain length
        if (domain.length < 3 || domain.length > 255) {
            return false;
        }

        // Prevent certain special characters in local part
        const specialCharsRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+$/;
        if (!specialCharsRegex.test(localPart)) {
            return false;
        }

        // Prevent consecutive dots
        if (/\.{2,}/.test(localPart) || /\.{2,}/.test(domain)) {
            return false;
        }

        // Prevent starting or ending with a dot
        if (localPart.startsWith('.') || localPart.endsWith('.') ||
            domain.startsWith('.') || domain.endsWith('.')) {
            return false;
        }

        return true;
    }

    // Bootstrap form validation setup
    function setupBootstrapValidation() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        const forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);

            // Remove validation classes on input
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    // Custom email validation
                    if (input.type === 'email') {
                        if (validateEmail(input.value.trim())) {
                            input.setCustomValidity('');
                        } else {
                            input.setCustomValidity('Invalid email address');
                        }
                    }
                });
            });
        });
    }

    // Handle form submission with custom validation
    if (forgotPasswordForm) {
        // Add Bootstrap validation classes
        forgotPasswordForm.classList.add('needs-validation');
        forgotPasswordForm.setAttribute('novalidate', true);

        // Setup custom email validation
        forgotPasswordEmail.addEventListener('input', function() {
            const email = this.value.trim();
            
            if (email === '') {
                this.setCustomValidity('Email is required');
            } else if (!validateEmail(email)) {
                this.setCustomValidity('Please enter a valid email address');
            } else {
                this.setCustomValidity('');
            }
            this.reportValidity();
        });

        // Setup form submission
        forgotPasswordForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                submitForgotPassword(this);
            }
            this.classList.add('was-validated');
        }, false);

        // Initial validation setup
        setupBootstrapValidation();
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
                // Set custom validity for server-side errors
                forgotPasswordEmail.setCustomValidity(data.message || 'Failed to send reset link');
                forgotPasswordEmail.reportValidity();
            }
        })
        .catch(error => {
            console.error('Forgot password error:', error);
            forgotPasswordEmail.setCustomValidity('An error occurred. Please try again.');
            forgotPasswordEmail.reportValidity();
        });
    }
});
