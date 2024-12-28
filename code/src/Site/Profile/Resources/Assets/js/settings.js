document.addEventListener('DOMContentLoaded', function() {
    // Menu item click handling
    const menuItems = document.querySelectorAll('.settings-menu-item');
    const sections = document.querySelectorAll('.settings-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-content');
            
            // Update active menu item
            menuItems.forEach(mi => mi.classList.remove('active'));
            this.classList.add('active');
            
            // Show target section, hide others
            sections.forEach(section => {
                section.style.display = section.id === targetId ? 'block' : 'none';
            });
        });
    });

    // Account deactivation handling
    document.getElementById('confirmDeactivate')?.addEventListener('click', function() {
        // Add your deactivation API call here
        console.log('Account deactivation confirmed');
        // After successful API call:
        $('#deactivateModal').modal('hide');
        // You might want to show a success message or redirect
    });

    // Account deletion handling
    document.getElementById('confirmDelete')?.addEventListener('click', function() {
        // Add your deletion API call here
        console.log('Account deletion confirmed');
        // After successful API call:
        $('#deleteModal').modal('hide');
        // You might want to show a success message or redirect
    });
});
