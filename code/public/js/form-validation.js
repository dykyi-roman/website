document.addEventListener('DOMContentLoaded', function() {
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
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        const inputs = registrationForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                // Only validate after user interaction
                input.dataset.touched = 'true';
                if (input.dataset.touched === 'true') {
                    validateField(input);
                }
            });
            
            input.addEventListener('blur', () => {
                // Validate on blur
                input.dataset.touched = 'true';
                validateField(input);
            });
        });

        // Form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            const inputs = this.querySelectorAll('input, select');
            let isValid = true;

            inputs.forEach(input => {
                input.dataset.touched = 'true';
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
                        // Hide registration popup
                        const registerPopup = document.getElementById('register-popup');
                        if (registerPopup) {
                            registerPopup.classList.remove('show');
                            document.body.classList.remove('popup-open');
                        }
                        
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
});
