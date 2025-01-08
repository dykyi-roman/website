document.addEventListener('DOMContentLoaded', async function() {
    // Get current language or default to English
    const currentLang = localStorage.getItem('locale') || 'en';
    const t = await loadTranslations(currentLang);
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
            hash = 'general'; // Default tab
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
    const modalOptions = {
        keyboard: true,
        backdrop: 'static',
        show: false
    };
    
    $('#deactivateModal, #deleteModal').modal(modalOptions);

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
                // After successful API call, switch to deactivation view
                $(this).closest('.action-block').html(`
                    <div id="deactivate-block">
                        <h3>${t.settings.deactivate_block.title}</h3>
                        <p class="action-description">
                            ${t.settings.deactivate_block.description}
                        </p>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#deactivateModal">
                            ${t.settings.deactivate_block.button}
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
                // After successful API call, switch to activation view
                $('#deactivate-block').closest('.action-block').html(`
                    <div id="activate-block">
                        <h3>${t.settings.activate_block.title}</h3>
                        <p class="action-description">
                            ${t.settings.activate_block.description}
                        </p>
                        <button class="btn btn-success" id="activateAccount">
                            ${t.settings.activate_block.button}
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
});
