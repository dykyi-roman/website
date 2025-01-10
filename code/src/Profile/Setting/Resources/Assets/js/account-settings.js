// Constants for image validation
const IMAGE_ALLOWED_TYPES = ['image/jpeg', 'image/png'];
const IMAGE_MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5MB

document.addEventListener('DOMContentLoaded', async function() {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);

    const form = document.querySelector('.account-settings');
    const imageInput = document.createElement('input');
    imageInput.type = 'file';
    imageInput.accept = IMAGE_ALLOWED_TYPES.join(',');
    imageInput.style.display = 'none';
    form.appendChild(imageInput);

    // Validation rules
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+\d{10,15}$/;
    const nameRegex = /^[a-zA-Z\s'-]{2,100}$/;

    const validationRules = {
        name: {
            validate: (value) => nameRegex.test(value.trim()),
            message: t.name_validation
        },
        email: {
            validate: (value) => emailRegex.test(value.trim()),
            message: t.error_invalid_email
        },
        tel: {
            validate: (value) => {
                const isValidFormat = phoneRegex.test(value.trim());
                const cleanedValue = value.replace(/\D/g, '');
                const isValidLength = cleanedValue.length >= 10 && cleanedValue.length <= 15;
                return isValidFormat && isValidLength;
            },
            message: t.phone_validation
        }
    };

    // Validation functions
    function validateField(field) {
        const value = field.value;
        const fieldType = field.type;
        const config = getValidationConfig(field, value);
        
        if (!config) return true;

        let isValid = true;
        let message = '';

        if (typeof config.validate === 'function') {
            const result = config.validate(value);
            if (typeof result === 'object') {
                isValid = result.isValid;
                message = result.message;
            } else {
                isValid = result;
                message = config.message;
            }
        }

        const feedback = field.parentElement.querySelector('.invalid-feedback') 
            || createFeedbackElement(field);
        
        updateFieldValidationState(field, feedback, isValid, message);
        return isValid;
    }

    function getValidationConfig(field, value) {
        const inputType = field.type;
        const name = field.name;

        return validationRules[name] || validationRules[inputType];
    }

    function createFeedbackElement(field) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentElement.appendChild(feedback);
        return feedback;
    }

    function updateFieldValidationState(field, feedback, isValid, message) {
        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid);
        
        if (!isValid) {
            feedback.textContent = message;
            feedback.style.display = 'block';
        } else {
            feedback.style.display = 'none';
        }
    }

    function setupFormValidation(form) {
        const inputs = form.querySelectorAll('input:not([type="file"])');
        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => validateField(input));

            if (input.type === 'tel') {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^+\d]/g, '');
                    if (!value.startsWith('+')) {
                        value = '+' + value.replace(/\+/g, '');
                    }
                    e.target.value = value;
                });
            }
        });
    }

    // Handle image upload
    const profileImage = document.querySelector('.item-image');
    const imageOverlay = document.querySelector('.image-overlay');
    
    imageOverlay.addEventListener('click', () => {
        imageInput.click();
    });

    imageInput.addEventListener('change', function(e) {
        const file = this.files[0];
        if (file) {
            // Check file type
            if (!IMAGE_ALLOWED_TYPES.includes(file.type)) {
                alert(t.error_invalid_image_type || 'Please select a JPEG or PNG image.');
                this.value = '';
                return;
            }

            // Check file size
            if (file.size > IMAGE_MAX_SIZE_BYTES) {
                alert(t.error_image_too_large || 'Image size should not exceed 5MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                profileImage.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Make fields editable
    const editableFields = ['name', 'email', 'phone'];
    editableFields.forEach(field => {
        const container = document.querySelector(`label[for="${field}"] strong`);
        if (container) {
            const value = container.textContent;
            const input = document.createElement('input');
            input.type = field === 'email' ? 'email' : 'text';
            input.value = value;
            input.className = 'form-control editable-input';
            input.name = field;
            container.parentNode.replaceChild(input, container);
        }
    });

    // Handle form submission
    const saveButton = document.getElementById('save-account-settings');
    saveButton.addEventListener('click', async function(e) {
        e.preventDefault();

        // Validate all fields
        const inputs = form.querySelectorAll('input:not([type="file"])');
        let isValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        if (!isValid) {
            return;
        }

        const formData = new FormData();
        if (imageInput.files[0]) {
            formData.append('photo', imageInput.files[0]);
        }

        inputs.forEach(input => {
            formData.append(input.name, input.value);
        });

        try {
            saveButton.disabled = true;
            const response = await fetch('/api/v1/users', {
                method: 'PUT',
                body: formData
            });

            if (response.ok) {
                const result = await response.json();
                // Show success message
                alert(t.settings_saved_successfully || 'Settings saved successfully!');
            } else {
                throw new Error('Failed to save settings');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(t.error_save_settings || 'Failed to save settings. Please try again.');
        } finally {
            saveButton.disabled = false;
        }
    });

    // Initialize form validation
    setupFormValidation(form);
});
