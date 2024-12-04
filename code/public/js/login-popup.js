document.addEventListener('DOMContentLoaded', function() {
    console.log('Login popup script loaded');

    // DOM Elements
    const loginModal = document.getElementById('loginModal');
    const loginForm = document.getElementById('loginForm');
    const closeBtn = document.getElementById('close-login-modal');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

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

    // Handle login form submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const rememberMe = document.getElementById('rememberMe').checked;

            // Clear previous errors
            clearAllErrors();

            // Validate form
            if (!validateForm(loginForm)) {
                return;
            }

            submitLogin(loginForm);
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
            clearAllErrors();
        }
    }

    function clearAllErrors() {
        const errorElements = loginForm.querySelectorAll('.error-message');
        errorElements.forEach(element => element.remove());
        const invalidInputs = loginForm.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
    }

    function validateForm(form) {
        let isValid = true;
        const email = form.querySelector('#loginEmail');
        const password = form.querySelector('#loginPassword');

        if (!email.value.trim()) {
            showError(email, 'Email is required');
            isValid = false;
        }
        if (!password.value.trim()) {
            showError(password, 'Password is required');
            isValid = false;
        }

        return isValid;
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
                window.location.reload();
            } else {
                showError(form.querySelector('#loginEmail'), data.message || 'Login failed');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            showError(form.querySelector('#loginEmail'), 'An error occurred during login');
        });
    }
});
