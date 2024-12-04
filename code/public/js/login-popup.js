document.addEventListener('DOMContentLoaded', function() {
    console.log('Login popup script loaded');

    // DOM Elements
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');
    const loginEmail = document.getElementById('loginEmail');
    const loginPassword = document.getElementById('loginPassword');
    const closeBtn = document.getElementById('close-login-modal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

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

    // Password validation
    function validatePassword(password) {
        // Check if password is empty
        if (!password) {
            return {
                valid: false,
                message: 'Password is required'
            };
        }

        // Check minimum length
        if (password.length < 8) {
            return {
                valid: false,
                message: 'Password must be at least 8 characters long'
            };
        }

        // Check for at least one uppercase letter
        if (!/[A-Z]/.test(password)) {
            return {
                valid: false,
                message: 'Password must contain at least one uppercase letter'
            };
        }

        // Check for at least one lowercase letter
        if (!/[a-z]/.test(password)) {
            return {
                valid: false,
                message: 'Password must contain at least one lowercase letter'
            };
        }

        // Check for at least one number
        if (!/[0-9]/.test(password)) {
            return {
                valid: false,
                message: 'Password must contain at least one number'
            };
        }

        // Check for at least one special character
        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            return {
                valid: false,
                message: 'Password must contain at least one special character'
            };
        }

        return { valid: true };
    }

    // Show error message
    function showError(input, message) {
        clearError(input);
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }

    // Clear error message
    function clearError(input) {
        input.classList.remove('is-invalid');
        const errorElement = input.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Event listener for login buttons
    const loginButtons = document.querySelectorAll('[data-action="login"]');
    loginButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(loginModal);
            modal.show();
        });
    });

    // Close button event
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(loginModal);
            modal.hide();
            resetForm();
        });
    }

    // Handle form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = loginEmail.value.trim();
            const password = loginPassword.value;

            // Clear previous errors
            clearError(loginEmail);
            clearError(loginPassword);

            // Validate email
            const emailValidation = validateEmail(email);
            if (!emailValidation.valid) {
                showError(loginEmail, emailValidation.message);
                return;
            }

            // Validate password
            const passwordValidation = validatePassword(password);
            if (!passwordValidation.valid) {
                showError(loginPassword, passwordValidation.message);
                return;
            }

            // Submit login form
            submitLogin(loginForm);
        });

        // Real-time email validation on blur
        loginEmail.addEventListener('blur', function() {
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

        // Real-time password validation on blur
        loginPassword.addEventListener('blur', function() {
            const password = this.value;
            
            // Clear previous errors
            clearError(this);

            // Validate password if not empty
            if (password) {
                const validationResult = validatePassword(password);
                if (!validationResult.valid) {
                    showError(this, validationResult.message);
                }
            }
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

    function resetForm() {
        if (loginForm) {
            loginForm.reset();
            clearError(loginEmail);
            clearError(loginPassword);
        }
    }

    // Submit login form
    function submitLogin(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        fetch('/login', {
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
                // Close modal and potentially redirect
                const modal = bootstrap.Modal.getInstance(loginModal);
                modal.hide();
                
                // Redirect or reload page
                window.location.reload();
            } else {
                // Show error message
                showError(loginEmail, data.message || 'Login failed');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            showError(loginEmail, 'An error occurred. Please try again.');
        });
    }
});
