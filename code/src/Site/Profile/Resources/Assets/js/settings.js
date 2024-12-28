$(document).ready(function() {
    // Function to switch active tab
    function switchTab(tabId) {
        // Update active menu item
        $('.settings-menu-item').removeClass('active');
        $(`[data-content="${tabId}"]`).addClass('active');
        
        // Show target section, hide others
        $('.settings-section').hide();
        $('#' + tabId).show();
        
        // Update URL hash
        window.location.hash = tabId;
    }

    // Handle initial page load
    function handleInitialLoad() {
        let hash = window.location.hash.substring(1); // Remove the # symbol
        if (!hash || !$('#' + hash).length) {
            hash = 'account'; // Default tab
        }
        switchTab(hash);
    }

    // Menu item click handling
    $('.settings-menu-item').on('click', function(e) {
        e.preventDefault();
        const targetId = $(this).attr('data-content');
        switchTab(targetId);
    });

    // Initialize Bootstrap modals
    $('#deactivateModal, #deleteModal').modal({
        keyboard: true,
        backdrop: 'static',
        show: false
    });

    // Handle modal backdrop cleanup
    $('#deactivateModal, #deleteModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });

    // Account deactivation button click
    $('[data-target="#deactivateModal"]').on('click', function(e) {
        e.preventDefault();
        $('#deactivateModal').modal('show');
    });

    // Account deletion button click
    $('[data-target="#deleteModal"]').on('click', function(e) {
        e.preventDefault();
        $('#deleteModal').modal('show');
    });

    // Account deactivation confirmation
    $('#confirmDeactivate').on('click', function() {
        // Add your deactivation API call here
        console.log('Account deactivation confirmed');
        // After successful API call:
        $('#deactivateModal').modal('hide');
        // You might want to show a success message or redirect
    });

    // Account deletion confirmation
    $('#confirmDelete').on('click', function() {
        // Add your deletion API call here
        console.log('Account deletion confirmed');
        // After successful API call:
        $('#deleteModal').modal('hide');
        // You might want to show a success message or redirect
    });

    // Handle URL hash changes
    $(window).on('hashchange', function() {
        handleInitialLoad();
    });

    // Initial load
    handleInitialLoad();
});
