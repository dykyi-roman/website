document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const popup = document.getElementById('registerPopup');
    if (!popup) return;

    const closeBtn = popup.querySelector('.close');
    const registrationOptions = popup.querySelector('.registration-options');
    const registerOptions = popup.querySelectorAll('.register-option');
    const registrationForm = document.getElementById('registrationForm');
    const registrationType = document.getElementById('registrationType');

    // Validation patterns
    const validationRules = {
        name: {
            pattern: /^[a-zA-Z\s]{2,50}$/,
            message: 'Name must be 2-50 characters long and contain only letters'
        },
        phone: {
            pattern: /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/,
            message: 'Please enter a valid phone number'
        },
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        }
    };

    // Event Listeners for Register button
    document.addEventListener('click', function(e) {
        const registerBtn = e.target.closest('[data-action="register"]');
        if (registerBtn) {
            showPopup();
        }
    });

    // Close button and outside click
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
    }
    
    window.addEventListener('click', (e) => {
        if (e.target === popup) {
            hidePopup();
        }
    });

    // Register option buttons
    registerOptions.forEach(option => {
        option.addEventListener('click', () => {
            const type = option.dataset.type;
            showRegistrationForm(type);
            registrationOptions.style.display = 'none';
        });
    });

    // Custom validation function
    function validateField(input) {
        const name = input.name;
        const value = input.value.trim();

        // Check if field is empty
        if (input.hasAttribute('required') && value === '') {
            showError(input, 'This field is required');
            return false;
        }

        // Check specific validation rules
        if (validationRules[name]) {
            if (!validationRules[name].pattern.test(value)) {
                showError(input, validationRules[name].message);
                return false;
            }
        }

        clearError(input);
        return true;
    }

    // Show error message
    function showError(input, message) {
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            input.classList.add('is-invalid');
        }
    }

    // Clear error message
    function clearError(input) {
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.style.display = 'none';
            input.classList.remove('is-invalid');
        }
    }

    // Add event listeners for real-time validation
    if (registrationForm) {
        const inputs = registrationForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => validateField(input));
            input.addEventListener('change', () => validateField(input));
        });

        // Form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            const inputs = this.querySelectorAll('input, select');
            let isValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            // If all fields are valid, submit the form
            if (isValid) {
                const formData = new FormData(this);
                
                fetch('/register', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        hidePopup();
                        // Show success message or redirect
                        alert('Registration successful!');
                    } else {
                        // Handle server-side validation errors
                        Object.keys(data.errors || {}).forEach(field => {
                            const input = registrationForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                showError(input, data.errors[field]);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Registration error:', error);
                    alert('An error occurred during registration. Please try again.');
                });
            }
        });
    }

    // Functions
    function showPopup() {
        popup.style.display = 'block';
        registrationForm.style.display = 'none';
        registrationOptions.style.display = 'flex';
        document.querySelectorAll('.register-option').forEach(btn => {
            btn.classList.remove('active');
        });
    }

    function hidePopup() {
        popup.style.display = 'none';
        registrationForm.reset(); // Reset form on close
        registrationOptions.style.display = 'flex';
    }

    function showRegistrationForm(type) {
        registrationType.value = type;
        document.querySelectorAll('.register-option').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-type="${type}"]`).classList.add('active');
        registrationForm.style.display = 'block';
    }
});
