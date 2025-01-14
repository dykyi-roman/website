document.addEventListener('DOMContentLoaded', async function () {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // DOM Elements
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const validationRules = {
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.placeholder_email
        },
        password: {
            validate: (value) => value.trim().length >= 8,
            message: t.label_password
        }
    };

    // Field validation function
    function validateField(field) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Skip validation for remember_me field
        if (fieldName === 'remember_me') {
            return true;
        }

        // Determine which validation rule to use
        if (field.type === 'email') {
            rule = validationRules.email;
        } else if (field.type === 'password') {
            rule = validationRules.password;
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
            if (feedback && feedback.classList.contains('invalid-feedback') && field.type !== 'checkbox') {
                feedback.textContent = rule.message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback && field.type !== 'checkbox') {
                feedback.textContent = '';
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
            input.addEventListener('input', function () {
                validateField(this);
            });

            // Validate on blur
            input.addEventListener('blur', function () {
                validateField(this);
            });

            // Initial validation state
            if (input.value) {
                validateField(input);
            }
        });
    }

    // Setup validation for login form
    setupFormValidation(loginForm);

    // Setup password toggle functionality
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        // Create wrapper div
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        
        // Move the input and its feedback to the wrapper
        const feedback = input.nextElementSibling;
        wrapper.appendChild(input);
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            wrapper.appendChild(feedback);
        }
    });

    // Form submission handler
    async function submitLogin(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input');

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (isValid) {
            try {
                // Show spinner
                showModalSpinner(loginModal);

                const formData = new FormData(form);
                const rememberMeCheckbox = form.querySelector('#rememberMe');
                // Explicitly set the remember_me value as a string "true" or "false"
                formData.set('remember_me', rememberMeCheckbox.checked ? 'true' : 'false');
                
                const response = await fetch('/login', {
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
                    hideModalSpinner(loginModal);

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

                    throw new Error(result.errors.message || t.error_invalid_credentials);
                }

                if (result.success) {
                    hideModalSpinner(loginForm);

                    window.location.href = result.redirectUrl || '/';
                } else {
                    hideModalSpinner(loginModal);

                    // Handle specific error messages
                    if (result.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.setCustomValidity(data.errors[field]);
                                input.reportValidity();
                            }
                        });
                    } else {
                        showErrorMessage(result.message || t.error_login_failed);
                    }
                }
            } catch (error) {
                hideModalSpinner(loginModal);
                showErrorMessage(error.message || t.error_network);
            }
        }
    }

    // Function to show error messages
    function showErrorMessage(message) {
        UIService.showError(message);
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

    // Event listener for login buttons
    const loginButtons = document.querySelectorAll('[data-action="login"]');
    loginButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(loginModal);
            modal.show();

            // Clear any previous errors
            clearErrors(loginForm);

            // Ensure Remember Me checkbox is checked
            const rememberMeCheckbox = document.getElementById('rememberMe');
            if (rememberMeCheckbox) {
                rememberMeCheckbox.checked = true;
            }

            const inputs = loginModal.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid', 'is-valid');
            });
        });
    });

    // Get registration type buttons
    const registerFacebookBtn = document.querySelector('.register-type-btn.login-facebook');

    // Facebook registration handler
    if (registerFacebookBtn) {
        registerFacebookBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const width = 600;
            const height = 600;
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;

            const popup = window.open('/connect/facebook', 'facebook_login',
                `width=${width},height=${height},left=${left},top=${top},` +
                'toolbar=no,menubar=no,scrollbars=yes,status=no,location=no'
            );

            // Check if popup was blocked
            if (!popup || popup.closed || typeof popup.closed == 'undefined') {
                alert('Please enable popups for this site to use Facebook login');
                return;
            }

            // Close the login modal
            const modal = new bootstrap.Modal(loginModal);
            modal.hide();

            // Handle popup window close
            const checkPopup = setInterval(() => {
                if (popup.closed) {
                    clearInterval(checkPopup);
                    window.location.reload(); // Refresh the parent window
                }
            }, 1000);

            // Add message listener for successful auth
            window.addEventListener('message', function(e) {
                if (e.data === 'oauth-success') {
                    popup.close();
                    window.location.reload();
                }
            }, false);
        });
    }

    // Add event listener for Google registration button
    const registerGoogleBtn = document.querySelector('.register-type-btn.login-google');
    if (registerGoogleBtn) {
        registerGoogleBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const width = 600;
            const height = 600;
            const left = (window.innerWidth - width) / 2;
            const top = (window.innerHeight - height) / 2;

            // Open Google OAuth popup
            const googleAuthWindow = window.open('/connect/google', 'Google Registration',
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`);

            // Check if popup was blocked
            if (!googleAuthWindow || googleAuthWindow.closed || typeof googleAuthWindow.closed == 'undefined') {
                alert('Please enable popups for this site to use Google login');
                return;
            }

            // Close the login modal
            const modal = new bootstrap.Modal(loginModal);
            modal.hide();

            // Handle popup window close
            const checkGooglePopup = setInterval(() => {
                if (googleAuthWindow.closed) {
                    clearInterval(checkGooglePopup);
                    window.location.reload(); // Refresh the parent window
                }
            }, 1000);

            // Add message listener for successful auth
            window.addEventListener('message', function(e) {
                if (e.data === 'oauth-success') {
                    googleAuthWindow.close();
                    window.location.reload();
                }
            }, false);

            // Focus on the popup window
            googleAuthWindow.focus();
        });
    }

    // Modal events
    loginModal.addEventListener('hidden.bs.modal', function () {
        loginForm.reset();
        clearErrors(loginForm);
    });

    // Form submission event
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            submitLogin(this);
        });
    }
});
