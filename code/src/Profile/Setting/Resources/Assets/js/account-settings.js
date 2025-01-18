// import { UIService, ModalService } from '../../../../../Site/Dashboard/Resources/Assets/js/base.js';

// Constants
const CONFIG = {
    IMAGE: {
        ALLOWED_TYPES: ['image/jpeg', 'image/png'],
        MAX_SIZE_BYTES: 5 * 1024 * 1024, // 5MB
    },
    VALIDATION: {
        EMAIL_REGEX: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        PHONE_REGEX: /^\+\d{10,15}$/,
        NAME_REGEX: /^[a-zA-Z\s'-]{2,100}$/,
    },
    UI: {
        VERIFICATION_TIMER: 90, // seconds
        NAME_MAX_LENGTH: 20,
        DEBOUNCE_DELAY: 500, // ms
    }
};

// Utility function to debounce function calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

class ValidationService {
    constructor(translations) {
        this.t = translations;
        this.rules = {
            name: {
                validate: (value) => CONFIG.VALIDATION.NAME_REGEX.test(value.trim()),
                message: this.t.name_validation
            },
            email: {
                validate: (value) => CONFIG.VALIDATION.EMAIL_REGEX.test(value.trim()),
                message: this.t.error_invalid_email
            },
            tel: {
                validate: (value) => {
                    const isValidFormat = CONFIG.VALIDATION.PHONE_REGEX.test(value.trim());
                    const cleanedValue = value.replace(/\D/g, '');
                    const isValidLength = cleanedValue.length >= 10 && cleanedValue.length <= 15;
                    return isValidFormat && isValidLength;
                },
                message: this.t.phone_validation
            }
        };
    }

    validateField(field) {
        const value = field.value;
        const config = this.rules[field.name] || this.rules[field.type];
        if (!config) return true;

        const isValid = config.validate(value);
        const feedback = this._getFeedbackElement(field);
        this._updateFieldState(field, feedback, isValid, config.message);
        return isValid;
    }

    _getFeedbackElement(field) {
        return field.parentElement.querySelector('.invalid-feedback') 
            || this._createFeedbackElement(field);
    }

    _createFeedbackElement(field) {
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentElement.appendChild(feedback);
        return feedback;
    }

    _updateFieldState(field, feedback, isValid, message) {
        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid);
        feedback.textContent = message;
        feedback.style.display = isValid ? 'none' : 'block';
    }
}

class UIManager {
    constructor(translations) {
        this.t = translations;
    }

    updateUserStatus(isActive) {
        const statusBadge = document.querySelector('.user-status .badge');
        if (statusBadge) {
            statusBadge.classList.remove('text-bg-success', 'text-bg-warning');
            statusBadge.classList.add(isActive ? 'text-bg-success' : 'text-bg-warning');
            statusBadge.textContent = isActive ? this.t.account?.active || 'Active' : this.t.account?.inactive || 'Inactive';
        }
    }

    updateProfileName(name, options = {}) {
        const {
            selector = '.profile-name',
            truncate = true,
            maxLength = CONFIG.UI.NAME_MAX_LENGTH
        } = options;

        const element = typeof selector === 'string' 
            ? document.querySelector(selector) 
            : selector;

        if (!element) {
            console.warn('Profile name element not found');
            return;
        }

        element.textContent = truncate
            ? this._truncateName(name, maxLength)
            : name;
    }

    _truncateName(name, maxLength) {
        return name.length > maxLength ? name.substring(0, maxLength - 3) + '...' : name;
    }
}

class ImageHandler {
    constructor(config, uiManager) {
        this.config = config;
        this.uiManager = uiManager;
        this.pendingImage = null;
    }

    setupImageUpload(profileImage, imageOverlay, imageInput, onImageChange) {
        imageOverlay.addEventListener('click', () => imageInput.click());
        imageInput.addEventListener('change', (e) => this._handleImageChange(e, profileImage, onImageChange));
    }

    _handleImageChange(event, profileImage, callback) {
        const file = event.target.files[0];
        if (!file) return;

        if (!this._validateImage(file)) {
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            profileImage.src = e.target.result;
            this.pendingImage = file;
            callback?.(file);
        };
        reader.readAsDataURL(file);
    }

    _validateImage(file) {
        if (!this.config.IMAGE.ALLOWED_TYPES.includes(file.type)) {
            UIService.showError(this.uiManager.t.settings?.error_image_type || 'Please select a JPEG or PNG image.');
            return false;
        }

        if (file.size > this.config.IMAGE.MAX_SIZE_BYTES) {
            UIService.showError(this.uiManager.t.settings?.error_image_size || 'Image size should not exceed 5MB.');
            return false;
        }

        return true;
    }
}

class VerificationHandler {
    constructor(translations, uiManager) {
        this.t = translations;
        this.uiManager = uiManager;
        this.verificationInProgress = false;
        this.debouncedHandleVerification = debounce(
            this.handleVerification.bind(this),
            CONFIG.UI.DEBOUNCE_DELAY
        );
    }

    async handleVerification(type) {
        if (this.verificationInProgress) {
            return false;
        }

        try {
            this.verificationInProgress = true;
            const response = await fetch('/api/v1/profile/verifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                UIService.showError(data.errors.message);
                console.log('Verification error:', data.errors.message);
                return false;
            }

            return true;
        } catch (error) {
            UIService.showError(this.uiManager.t.settings.error_sending_verification_code);
            console.error('Verification error:', error);
            return false;
        } finally {
            this.verificationInProgress = false;
        }
    }

    async verifyCode(type, code, modal) {
        if (this.verificationInProgress) {
            return;
        }

        this.verificationInProgress = true;
        ModalService.showSpinner();

        try {
            const response = await fetch(`/api/v1/profile/verifications/${type}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code })
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(this.t.settings.error_verifying);
            }

            this._updateVerificationUI(type);
            this._closeVerificationModal(type);

        } catch (error) {
            UIService.showError(this.t.settings.error_verifying);
            console.error('Verification error:', error);
        } finally {
            this.verificationInProgress = false;
            ModalService.hideSpinner()
        }
    }

    _updateVerificationUI(type) {
        const inputContainer = document.querySelector(`#${type}`).closest('.input-group');
        const existingVerifyButton = inputContainer.querySelector('.verify-button');
        if (existingVerifyButton) {
            existingVerifyButton.remove();
        }

        const successIcon = `
            <span class="text-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.8 11.8 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7 7 0 0 0 1.048-.625 11.8 11.8 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.54 1.54 0 0 0-1.044-1.263 63 63 0 0 0-2.887-.87C9.843.266 8.69 0 8 0m2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793z"/>
                </svg>
            </span>`;
        
        inputContainer.querySelector('.input-group-append').insertAdjacentHTML('beforeend', successIcon);
    }

    _closeVerificationModal(type) {
        const modalId = `verify${type.charAt(0).toUpperCase() + type.slice(1)}Modal`;
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById(modalId));
        modalInstance.hide();
    }
}

class AccountSettingsManager {
    constructor() {
        this.init();
    }

    async init() {
        const currentLang = localStorage.getItem('locale') || 'en';
        this.t = await loadTranslations(currentLang);
        
        this.uiManager = new UIManager(this.t);
        this.validationService = new ValidationService(this.t);
        this.imageHandler = new ImageHandler(CONFIG, this.uiManager);
        this.verificationHandler = new VerificationHandler(this.t, this.uiManager);
        
        this.form = document.querySelector('.account-settings');
        this.saveButton = document.getElementById('save-account-settings');
        
        this._setupFormElements();
        this._setupEventListeners();
        this._initializeVerification();
        
        this._updateSaveButtonState();
    }

    _setupFormElements() {
        const imageInput = document.createElement('input');
        imageInput.type = 'file';
        imageInput.accept = CONFIG.IMAGE.ALLOWED_TYPES.join(',');
        imageInput.style.display = 'none';
        this.form.appendChild(imageInput);

        const profileImage = document.querySelector('.item-image');
        const imageOverlay = document.querySelector('.image-overlay');
        this.imageHandler.setupImageUpload(profileImage, imageOverlay, imageInput, 
            () => this.saveButton.disabled = false);

        ['name', 'email', 'phone'].forEach(fieldName => {
            const input = this.form.querySelector(`#${fieldName}`);
            if (input) {
                input.setAttribute('data-original-value', input.value.trim());
                ['change', 'input'].forEach(eventType => {
                    input.addEventListener(eventType, () => {
                        this._updateSaveButtonState();
                        this.validationService.validateField(input);
                    });
                });
            }
        });
    }

    _setupEventListeners() {
        this.saveButton.addEventListener('click', (e) => this._handleSave(e));
        
        document.addEventListener('userStatusChanged', (event) => {
            if (event.detail && typeof event.detail.isActive === 'boolean') {
                this.uiManager.updateUserStatus(event.detail.isActive);
            }
        });
    }

    _initializeVerification() {
        const emailInput = document.querySelector('#email');
        const phoneInput = document.querySelector('#phone');

        if (emailInput) {
            const emailVerifyButton = document.querySelector('.verify-email-button');
            if (emailVerifyButton) {
                this._setupVerificationButton('email', emailInput, emailVerifyButton);
            }
        }

        if (phoneInput) {
            const phoneVerifyButton = document.querySelector('.verify-phone-button');
            if (phoneVerifyButton) {
                this._setupVerificationButton('phone', phoneInput, phoneVerifyButton);
            }
        }
    }

    _setupVerificationButton(type, input, button) {
        // Add input validation listener
        const validateInput = () => {
            button.disabled = type === 'email'
                ? !this._validateEmail(input.value)
                : !this._validatePhone(input.value);
        };
        
        input.addEventListener('input', validateInput);
        validateInput(); // Initial validation

        // Add click handler with debounce
        const debouncedClickHandler = debounce(
            async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                if (button.disabled) return;
                
                button.disabled = true;
                
                const modalElement = document.getElementById(`verify${type.charAt(0).toUpperCase() + type.slice(1)}Modal`);
                const modal = new bootstrap.Modal(modalElement);
                
                if (await this.verificationHandler.handleVerification(type)) {
                    modal.show();
                    this._setupVerificationCodeHandling(type, modalElement);
                } else {
                    button.disabled = false;
                }
            },
            CONFIG.UI.DEBOUNCE_DELAY
        );

        button.addEventListener('click', debouncedClickHandler);
    }

    async _handleSave(e) {
        e.preventDefault();
        
        try {
            if (!this._validateForm()) {
                throw new Error(this.t.settings.form_validation_error);
            }

            const payload = this._getFormData();
            const response = await this._saveFormData(payload);

            if (!response.success) {
                throw new Error(response.errors?.message || this.t.settings.update_failed);
            }

            this._updateAfterSuccessfulSave();

        } catch (error) {
            UIService.showError(this.t.settings.unexpected_error);
        }
    }

    _validateForm() {
        return ['name', 'email', 'phone'].every(fieldName => {
            const input = this.form.querySelector(`#${fieldName}`);
            return input && this.validationService.validateField(input);
        });
    }

    _getFormData() {
        const payload = {};
        ['name', 'email', 'phone'].forEach(fieldName => {
            const input = this.form.querySelector(`#${fieldName}`);
            if (input) {
                payload[fieldName] = String(input.value.trim());
            }
        });
        return payload;
    }

    async _saveFormData(payload) {
        const response = await fetch('/api/v1/users/self', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        return await response.json();
    }

    _updateAfterSuccessfulSave() {
        const changedFields = {
            email: false,
            phone: false
        };

        ['email', 'phone'].forEach(fieldName => {
            const input = this.form.querySelector(`#${fieldName}`);
            if (input) {
                changedFields[fieldName] = input.value.trim() !== input.getAttribute('data-original-value');
                input.setAttribute('data-original-value', input.value.trim());
            }
        });

        const nameInput = this.form.querySelector('#name');
        if (nameInput) {
            nameInput.setAttribute('data-original-value', nameInput.value.trim());
            this.uiManager.updateProfileName(nameInput.value.trim());
        }

        // If email/phone changed, show verify button
        ['email', 'phone'].forEach(fieldType => {
            if (changedFields[fieldType]) {
                const inputContainer = document.querySelector(`#${fieldType}`).closest('.input-group');
                const appendContainer = inputContainer.querySelector('.input-group-append');
                
                // Remove success icon if exists
                const successIcon = appendContainer.querySelector('.text-success');
                if (successIcon) {
                    successIcon.remove();
                }

                // Add verify button if doesn't exist
                if (!appendContainer.querySelector(`.verify-${fieldType}-button`)) {
                    const verifyButton = document.createElement('button');
                    verifyButton.type = 'button';
                    verifyButton.className = `verify-button verify-${fieldType}-button`;
                    verifyButton.textContent = this.t.settings?.verify || 'Verify';
                    appendContainer.appendChild(verifyButton);

                    // Setup the new verify button
                    const input = document.querySelector(`#${fieldType}`);
                    this._setupVerificationButton(fieldType, input, verifyButton);
                }
            }
        });

        this.saveButton.disabled = true;
    }

    _hasFormChanges() {
        return ['name', 'email', 'phone'].some(fieldName => {
            const input = this.form.querySelector(`#${fieldName}`);
            if (input) {
                const originalValue = input.getAttribute('data-original-value') || '';
                return input.value.trim() !== originalValue;
            }
            return false;
        });
    }

    _updateSaveButtonState() {
        this.saveButton.disabled = !this._hasFormChanges() || !this._validateForm();
    }

    _setupVerificationCodeHandling(type, modalElement) {
        const inputs = modalElement.querySelectorAll('.verification-code-input input');
        
        inputs.forEach(input => {
            input.value = '';
        });
        
        inputs.forEach((input, index) => {
            const handleInput = (e) => {
                const value = e.target.value;
                
                if (value.length > 1) {
                    e.target.value = value.slice(-1);
                }
                
                if (!/^\d*$/.test(e.target.value)) {
                    e.target.value = '';
                    return;
                }

                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                const values = Array.from(inputs).map(input => input.value);
                const allInputsFilled = values.every(val => val.length === 1);

                if (allInputsFilled) {
                    const code = values.join('');
                    const modalContent = modalElement.querySelector('.modal-content');
                    this.verificationHandler.verifyCode(type, code, modalContent);
                }
            };

            input.addEventListener('input', handleInput);

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/\D/g, '').split('');

                inputs.forEach((input, i) => {
                    if (i >= index && pastedData[i - index]) {
                        input.value = pastedData[i - index];
                        if (i < inputs.length - 1) {
                            inputs[i + 1].focus();
                        }
                    }
                });

                const values = Array.from(inputs).map(input => input.value);
                const allInputsFilled = values.every(val => val.length === 1);

                if (allInputsFilled) {
                    const code = values.join('');
                    const modalContent = modalElement.querySelector('.modal-content');
                    this.verificationHandler.verifyCode(type, code, modalContent);
                }
            });
        });

        const timerContainer = document.createElement('div');
        timerContainer.className = 'verification-timer text-center w-100 mt-3';
        modalElement.querySelector('.modal-body').appendChild(timerContainer);
        
        this._startVerificationTimer(CONFIG.UI.VERIFICATION_TIMER, timerContainer, type);

        const oldListener = modalElement._hideListener;
        if (oldListener) {
            modalElement.removeEventListener('hidden.bs.modal', oldListener);
        }

        const hideListener = () => {
            const button = document.querySelector(`.verify-${type}-button`);
            if (button) {
                const input = document.querySelector(`#${type}`);
                button.disabled = type === 'email' 
                    ? !this._validateEmail(input.value)
                    : !this._validatePhone(input.value);
            }
            timerContainer.remove();
        };

        modalElement._hideListener = hideListener;
        modalElement.addEventListener('hidden.bs.modal', hideListener);
    }

    _startVerificationTimer(duration, container, type) {
        if (!container) return;
        
        let timeLeft = duration;
        
        const updateTimer = () => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                container.innerHTML = `<a href="#" class="resend-verification">${this.t.settings.send_again_verification_code}</a>`;
                
                const resendLink = container.querySelector('.resend-verification');
                if (resendLink) {
                    const debouncedResend = debounce(
                        async (e) => {
                            e.preventDefault();
                            const success = await this.verificationHandler.handleVerification(type);
                            if (success) {
                                resendLink.remove();
                                this._startVerificationTimer(CONFIG.UI.VERIFICATION_TIMER, container, type);
                            }
                        },
                        CONFIG.UI.DEBOUNCE_DELAY
                    );
                    resendLink.addEventListener('click', debouncedResend);
                }
                return;
            }

            container.textContent = this.t.settings.verification_retry_timer.replace('%seconds%', timeLeft);
            timeLeft--;
        };

        updateTimer();
        const timer = setInterval(updateTimer, 1000);
        return timer;
    }

    _validateEmail(email) {
        return CONFIG.VALIDATION.EMAIL_REGEX.test(email);
    }

    _validatePhone(phone) {
        return phone.replace(/\D/g, '').length >= 10;
    }
}

document.addEventListener('DOMContentLoaded', () => new AccountSettingsManager());
