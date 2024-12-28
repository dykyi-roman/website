$(document).ready(function() {
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

    // Account deactivation button click
    $('[data-target="#deactivateModal"]').on('click', function(e) {
        e.preventDefault();
        closeModal('#deleteModal'); // Close other modal if open
        $('#deactivateModal').modal('show');
    });

    // Account deletion button click
    $('[data-target="#deleteModal"]').on('click', function(e) {
        e.preventDefault();
        closeModal('#deactivateModal'); // Close other modal if open
        $('#deleteModal').modal('show');
    });

    // Account deactivation confirmation
    $('#confirmDeactivate').on('click', function() {
        // Add your deactivation API call here
        console.log('Account deactivation confirmed');
        closeModal('#deactivateModal');
    });

    // Account deletion confirmation
    $('#confirmDelete').on('click', function() {
        // Add your deletion API call here
        console.log('Account deletion confirmed');
        closeModal('#deleteModal');
    });

    // Handle modal hidden event
    $('.modal').on('hidden.bs.modal', function() {
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
