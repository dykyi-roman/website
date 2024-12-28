document.addEventListener('DOMContentLoaded', function () {
    const menuItems = document.querySelectorAll('.settings-menu-item');
    const sections = document.querySelectorAll('.settings-section');

    menuItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all menu items
            menuItems.forEach(mi => mi.classList.remove('active'));
            // Add active class to clicked menu item
            this.classList.add('active');

            // Hide all sections
            sections.forEach(section => section.style.display = 'none');

            // Show selected section
            const contentId = this.getAttribute('data-content');
            const contentSection = document.getElementById(contentId);
            if (contentSection) {
                contentSection.style.display = 'block';
            }

            // Update URL hash
            window.location.hash = contentId;
        });
    });

    // Handle initial load and browser back/forward
    function handleHashChange() {
        const hash = window.location.hash.slice(1) || 'account';
        const targetMenuItem = document.querySelector(`[data-content="${hash}"]`);
        if (targetMenuItem) {
            targetMenuItem.click();
        }
    }

    window.addEventListener('hashchange', handleHashChange);
    handleHashChange(); // Handle initial load
});