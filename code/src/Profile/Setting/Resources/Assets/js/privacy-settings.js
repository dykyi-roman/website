// Password regex for complexity
const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/;

// Store translations globally
let translations;

// Field validation function
function validateField(field) {
    const value = field.value.trim();
    const formGroup = field.closest('.form-group');
    const feedback = formGroup.querySelector('.invalid-feedback');

    // Password validation
    if (field.type === 'password') {
        if (value.length < 8) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback) {
                feedback.style.display = 'block';
                feedback.textContent = translations?.password_length_validation || 'Password must be at least 8 characters long';
            }
            return false;
        }

        if (!passwordRegex.test(value)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback) {
                feedback.style.display = 'block';
                feedback.textContent = translations?.password_complexity_validation || 'Password must include letters, numbers, and special characters';
            }
            return false;
        }
    }

    // Field is valid
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    if (feedback) {
        feedback.style.display = 'none';
        feedback.textContent = '';
    }
    return true;
}

// Setup password toggle functionality
function setupPasswordToggles() {
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach(input => {
        // Create wrapper if not exists
        let wrapper = input.parentElement;
        if (!wrapper.classList.contains('password-wrapper')) {
            wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        // Create toggle button if not exists
        if (!wrapper.querySelector('.password-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle';
            toggleBtn.innerHTML = '<i class="fa fa-eye"></i>';
            
            toggleBtn.addEventListener('click', function() {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                toggleBtn.innerHTML = type === 'password' ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>';
            });
            
            wrapper.appendChild(toggleBtn);
        }
    });
}

// Initialize translations and setup password validation
async function initializeApp() {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    translations = await loadTranslations(currentLang);

    // Handle password change form submission
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        const currentPassword = document.getElementById('current-password');
        const newPassword = document.getElementById('new-password');

        // Add input validation listeners
        [currentPassword, newPassword].forEach(input => {
            if (input) {
                input.addEventListener('input', () => {
                    validateField(input);
                });
            }
        });

        changePasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const isCurrentPasswordValid = validateField(currentPassword);
            const isNewPasswordValid = validateField(newPassword);

            if (!isCurrentPasswordValid || !isNewPasswordValid) {
                return;
            }

            try {
                ModalService.showSpinner();
                const response = await fetch('/api/v1/settings/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        currentPassword: currentPassword.value,
                        newPassword: newPassword.value
                    })
                });

                if (response.ok) {
                    UIService.showSuccess(translations?.settings.password_changed_success);
                    $('#change-password-popup').modal('hide');
                    changePasswordForm.reset();
                } else {
                    const data = await response.json();
                    UIService.showError(translations?.settings.error_changing_password);
                }
            } catch (error) {
                UIService.showError(translations?.settings.error_changing_password);
                console.error('Error changing password:', error);
            } finally {
                ModalService.hideSpinner();
            }
        });
    }

    // Initialize Bootstrap modals
    const modalOptions = {
        keyboard: true,
        backdrop: 'static'
    };

    // Setup change password modal
    const changePasswordModal = document.getElementById('change-password-popup');
    if (changePasswordModal) {
        const modal = new bootstrap.Modal(changePasswordModal, modalOptions);
        
        // Add event listener for modal show
        changePasswordModal.addEventListener('show.bs.modal', () => {
            const form = document.getElementById('changePasswordForm');
            if (form) {
                form.reset();
                form.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('is-invalid', 'is-valid');
                });
                form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                    feedback.style.display = 'none';
                    feedback.textContent = '';
                });
            }
        });
    }

    // Function to switch active tab
    function switchTab(tabId, updateHash = true) {
        // Update active menu item
        $('.settings-menu-item').removeClass('active');
        $(`[data-content="${tabId}"]`).addClass('active');
        
        // Show target section, hide others
        $('.settings-section').hide();
        $('#' + tabId).show();
        
        // Update URL hash only if requested
        if (updateHash) {
            history.replaceState(null, null, '#' + tabId);
        }
    }

    // Handle initial page load
    function handleInitialLoad() {
        let hash = window.location.hash.substring(1); // Remove the # symbol
        if (!hash || !$('#' + hash).length) {
            hash = 'account'; // Default tab
        }
        switchTab(hash, false);
    }

    // Menu item click handling
    $('.settings-menu-item').on('click', function(e) {
        e.preventDefault();
        const targetId = $(this).attr('data-content');
        switchTab(targetId);
    });

    // Function to properly close modal
    function closeModal(modalId) {
        // Remove error messages
        $(modalId + ' .alert').remove();
        $(modalId).modal('hide');
        $('.modal-backdrop').remove();
        $('body').css('padding-right', '');
    }

    // Initialize Bootstrap modals
    const modalOptions2 = {
        keyboard: true,
        backdrop: 'static',
        show: false
    };
    
    $('#deactivateModal, #deleteModal').modal(modalOptions2);

    // Handle modal close buttons
    $('.modal .close, .modal .btn-secondary').on('click', function(e) {
        e.preventDefault();
        const modalId = '#' + $(this).closest('.modal').attr('id');
        closeModal(modalId);
    });

    // Handle ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('#deactivateModal');
            closeModal('#deleteModal');
        }
    });

    // Use event delegation for modal trigger buttons
    $(document).on('click', '[data-target="#deactivateModal"]', function(e) {
        e.preventDefault();
        closeModal('#deleteModal'); // Close other modal if open
        // Clear any existing error messages
        $('#deactivateModal .alert').remove();
        $('#deactivateModal').modal('show');
    });

    $(document).on('click', '[data-target="#deleteModal"]', function(e) {
        e.preventDefault();
        closeModal('#deactivateModal'); // Close other modal if open
        // Clear any existing error messages
        $('#deleteModal .alert').remove();
        $('#deleteModal').modal('show');
    });


    // Use event delegation for dynamically added elements
    $(document).on('click', '#activateAccount', async function() {
        try {
            const response = await fetch('/settings/privacy/user-activate?status=1', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (!response.ok) {
                throw data;
            }
            if (data.success) {
                // Dispatch event to update user status UI
                document.dispatchEvent(new CustomEvent('userStatusChanged', { 
                    detail: { isActive: true }
                }));
                
                // After successful API call, switch to deactivation view
                $(this).closest('.action-block').html(`
                    <div id="deactivate-block">
                        <h3>${translations?.settings.deactivate_block.title}</h3>
                        <p class="action-description">
                            ${translations?.settings.deactivate_block.description}
                        </p>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#deactivateModal">
                            ${translations?.settings.deactivate_block.button}
                        </button>
                    </div>
                `);
            } else {
                console.error('Failed to activate account');
            }
        } catch (error) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger mt-3';
            alertDiv.textContent = error.errors?.message || 'An unexpected error occurred';
            console.error('Error:', error);
            
            const actionBlock = document.querySelector('.action-block');
            // Remove any existing alerts
            const existingAlert = actionBlock.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            actionBlock.insertBefore(alertDiv, actionBlock.firstChild);
        }
    });

    // Account deactivation confirmation
    $('#confirmDeactivate').on('click', async function() {
        try {
            const response = await fetch('/settings/privacy/user-activate?status=0', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (!response.ok) {
                throw data;
            }
            if (data.success) {
                closeModal('#deactivateModal');
                // Dispatch event to update user status UI
                document.dispatchEvent(new CustomEvent('userStatusChanged', { 
                    detail: { isActive: false }
                }));
                
                // After successful API call, switch to activation view
                $('#deactivate-block').closest('.action-block').html(`
                    <div id="activate-block">
                        <h3>${translations?.settings.activate_block.title}</h3>
                        <p class="action-description">
                            ${translations?.settings.activate_block.description}
                        </p>
                        <button class="btn btn-success" id="activateAccount">
                            ${translations?.settings.activate_block.button}
                        </button>
                    </div>
                `);
            } else {
                console.error('Failed to deactivate account');
            }
        } catch (error) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = error.errors?.message || 'An unexpected error occurred';
            console.error('Error:', error);
            
            const modalBody = document.querySelector('#deactivateModal .modal-body');
            // Remove any existing alerts
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            modalBody.insertBefore(alertDiv, modalBody.firstChild);
        }
    });

    // Account deletion confirmation
    $('#confirmDelete').on('click', async function() {
        try {
            const response = await fetch('/settings/privacy/account-delete', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (!response.ok) {
                throw data;
            }
            
            closeModal('#deleteModal');
            // Redirect to home page after account deletion
            window.location.href = '/';
        } catch (error) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.textContent = error.errors?.message || 'An unexpected error occurred';
            console.error('Error:', error);
            
            const modalBody = document.querySelector('#deleteModal .modal-body');
            // Remove any existing alerts
            const existingAlert = modalBody.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            modalBody.insertBefore(alertDiv, modalBody.firstChild);
        }
    });

    // Handle modal hidden event
    $('.modal').on('hidden.bs.modal', function() {
        // Remove error messages when modal is hidden
        $(this).find('.alert').remove();
        $('.modal-backdrop').remove();
        $('body').css('padding-right', '');
    });

    // Handle URL hash changes
    $(window).on('hashchange', function() {
        handleInitialLoad();
    });

    // Initial load
    handleInitialLoad();

    setupPasswordToggles();
}

// Add some CSS to ensure validation messages are visible
const style = document.createElement('style');
style.textContent = `
    .invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 80%;
        color: #dc3545;
    }

    .is-invalid ~ .invalid-feedback {
        display: block;
    }

    .password-wrapper {
        position: relative;
        display: block;
        width: 100%;
    }

    .password-wrapper .password-toggle {
        position: absolute;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        cursor: pointer;
        padding: 0;
        color: #6c757d;
        z-index: 2;
    }

    .password-wrapper .password-toggle:hover {
        color: #495057;
    }

    .password-wrapper .password-toggle:focus {
        outline: none;
    }

    .password-wrapper input[type="password"],
    .password-wrapper input[type="text"] {
        width: 100%;
        padding-right: 70px;
    }
`;
document.head.appendChild(style);

// Initialize the application when the DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    await initializeApp();
});
