document.addEventListener('DOMContentLoaded', async function() {
    console.log('Register popup script loaded');

    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    // Update labels
    const emailLabel = document.querySelector('label[for="registerEmail"]');

    // DOM Elements
    const popup = document.getElementById('register-popup');
    const registrationTypeSelection = document.getElementById('registration-type-selection');
    const clientForm = document.getElementById('clientRegistrationForm');
    const partnerForm = document.getElementById('partnerRegistrationForm');

    // Initialize Bootstrap modal
    const registerModal = new bootstrap.Modal(popup, {
        backdrop: 'static',
        keyboard: false
    });

    // Check if forms exist before proceeding
    if (!clientForm || !partnerForm) {
        console.error('Registration forms not found!');
        return;
    }

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[\d\s-()]{10,}$/;
    const nameRegex = /^[a-zA-Z\s'-]{2,50}$/;
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;

    // Validation rules for different field types
    const validationRules = {
        name: {
            validate: (value) => nameRegex.test(value.trim()),
            message: t.name_validation
        },
        partner_name: {
            validate: (value) => nameRegex.test(value.trim()),
            message: t.partner_name_validation
        },
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.error_invalid_email
        },
        tel: {
            validate: (value) => phoneRegex.test(value.trim()),
            message: t.phone_validation
        },
        select: {
            validate: (value) => value.trim() !== '',
            message: t.selection_validation
        },
        password: {
            validate: (value) => {
                const password = value.trim();
                if (password.length < 8) {
                    return { isValid: false, message: t.password_length_validation };
                }
                if (!passwordRegex.test(password)) {
                    return { isValid: false, message: t.password_complexity_validation };
                }
                return { isValid: true };
            },
            message: t.password_validation
        }
    };

    // Field validation function
    function validateField(field, form) {
        const value = field.value;
        const fieldName = field.name;
        let rule;

        // Determine which validation rule to use
        if (field.type === 'email') {
            rule = validationRules.email;
        } else if (field.type === 'tel') {
            rule = validationRules.tel;
        } else if (field.type === 'password') {
            rule = validationRules.password;
        } else if (field.tagName.toLowerCase() === 'select') {
            rule = validationRules.select;
        } else if (fieldName === 'name') {
            rule = validationRules.name;
        } else if (fieldName === 'partner_name') {
            rule = validationRules.partner_name;
        } else {
            // Default validation for required fields
            rule = {
                validate: (value) => ({ isValid: value.trim() !== '', message: t.field_required }),
                message: t.field_required
            };
        }

        const validationResult = rule.validate(value);
        const isValid = typeof validationResult === 'boolean' ? validationResult : validationResult.isValid;
        const message = typeof validationResult === 'boolean' ? rule.message : validationResult.message;
        
        const feedback = field.nextElementSibling;

        if (!isValid) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = message;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
            }
        }

        return isValid;
    }

    // Add validation to form fields
    function setupFormValidation(form) {
        if (!form) return;

        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            // Validate on input
            input.addEventListener('input', function() {
                validateField(this, form);
            });

            // Validate on blur
            input.addEventListener('blur', function() {
                validateField(this, form);
            });
        });
    }

    // Setup validation for both forms
    setupFormValidation(clientForm);
    setupFormValidation(partnerForm);

    // Form submission handler
    async function submitRegistration(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (!validateField(input, form)) {
                isValid = false;
            }
        });

        if (isValid) {
            submitForm(form);
        }
    }

    // Submit form data
    async function submitForm(form) {
        try {
            const formData = new FormData(form);
            const formDataObject = Object.fromEntries(formData.entries());
            
            // Convert FormData to JSON and ensure proper data structure
            const requestData = {
                name: formDataObject.name || formDataObject.partner_name,
                email: formDataObject.email,
                password: formDataObject.password,
                phone: formDataObject.phone || null,
                country: formDataObject.country || null,
                city: formDataObject.city || null
            };

            // Add the identification field
            if (formDataObject['partner-id']) {
                requestData['partner-id'] = formDataObject['partner-id'];
            } else if (formDataObject['client-id']) {
                requestData['client-id'] = formDataObject['client-id'];
            }

            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || t.error_registration_failed);
            }

            const data = await response.json();
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(registerModal);
                if (modal) {
                    modal.hide();
                }
                showSuccessMessage(t.registration_successful);
                // Redirect to login
                setTimeout(() => {
                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                }, 1500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.setCustomValidity(data.errors[field]);
                            input.reportValidity();
                        }
                    });
                } else {
                    showErrorMessage(data.message || t.error_registration_failed);
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            showErrorMessage(error.message || t.error_network);
        }
    }

    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('.modal-body').prepend(alertDiv);
    }

    // Event listeners for form submission
    clientForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitRegistration(this);
    });

    partnerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitRegistration(this);
    });

    // Event listener for register button
    const registerButtons = document.querySelectorAll('[data-action="register"]');
    registerButtons.forEach(button => {
        button.addEventListener('click', showRegistrationModal);
    });

    // Add event delegation for favorite buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-favorite')) {
            console.log('Favorite button clicked');
            showRegistrationModal();
        }
    });

    // Registration type selection
    const registerTypeButtons = document.querySelectorAll('.register-type-btn');
    registerTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            showRegistrationForm(type);
        });
    });

    // Switch to login popup
    const switchToLoginLink = document.getElementById('switch-to-login');
    if (switchToLoginLink) {
        switchToLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hide registration modal
            registerModal.hide();
            
            // Show login modal
            const loginModal = document.getElementById('loginModal');
            if (loginModal) {
                const loginModalInstance = new bootstrap.Modal(loginModal);
                loginModalInstance.show();
            }
        });
    }

    // Functions
    function showRegistrationModal() {
        console.log('Showing registration modal');
        resetForms();
        showRegistrationTypeSelection();
        registerModal.show();
    }

    function showRegistrationTypeSelection() {
        registrationTypeSelection.classList.remove('d-none');
        clientForm.classList.add('d-none');
        partnerForm.classList.add('d-none');
    }

    function showRegistrationForm(type) {
        registrationTypeSelection.classList.add('d-none');
        
        if (type === 'client') {
            clientForm.classList.remove('d-none');
            partnerForm.classList.add('d-none');
        } else if (type === 'partner') {
            partnerForm.classList.remove('d-none');
            clientForm.classList.add('d-none');
        }
    }

    function resetForms() {
        [clientForm, partnerForm].forEach(form => {
            form.reset();
            form.classList.remove('was-validated');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
                input.setCustomValidity('');
            });
        });
    }

    // Modal events
    popup.addEventListener('hidden.bs.modal', function () {
        resetForms();
        showRegistrationTypeSelection();
    });

    // City-Country relationship data
    const cityByCountry = {
        'us': ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Miami'],
        'ca': ['Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa'],
        'uk': ['London', 'Manchester', 'Birmingham', 'Liverpool', 'Edinburgh']
    };

    // Update city options based on selected country
    function updateCityOptions(countrySelect, citySelect) {
        const country = countrySelect.value;
        citySelect.innerHTML = '<option value="">Select City</option>';
        
        if (country && cityByCountry[country]) {
            cityByCountry[country].forEach(city => {
                const option = document.createElement('option');
                option.value = city.toLowerCase().replace(/\s+/g, '-');
                option.textContent = city;
                citySelect.appendChild(option);
            });
        }
    }

    // Add event listeners for country selects
    const clientCountrySelect = document.getElementById('client-country');
    const clientCitySelect = document.getElementById('client-city');
    const partnerCountrySelect = document.getElementById('partner-country');
    const partnerCitySelect = document.getElementById('partner-city');

    if (clientCountrySelect && clientCitySelect) {
        clientCountrySelect.addEventListener('change', () => {
            updateCityOptions(clientCountrySelect, clientCitySelect);
        });
    }

    if (partnerCountrySelect && partnerCitySelect) {
        partnerCountrySelect.addEventListener('change', () => {
            updateCityOptions(partnerCountrySelect, partnerCitySelect);
        });
    }

    // Initialize social login buttons
    function initSocialLogin() {
        const googleLoginBtns = document.querySelectorAll('.social-btn-google');
        const facebookLoginBtns = document.querySelectorAll('.social-btn-facebook');

        googleLoginBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                console.log('Google Login clicked');
            });
        });

        facebookLoginBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                console.log('Facebook Login clicked');
            });
        });
    }

    // Initialize social buttons
    initSocialLogin();
});
