// Order functionality
document.addEventListener('DOMContentLoaded', function() {
    const orderButton = document.getElementById('order-button');
    const orderMenu = document.getElementById('order-menu');
    const orderOptions = orderMenu.querySelectorAll('a');

    // Toggle order menu
    orderButton.addEventListener('click', function(e) {
        e.stopPropagation();
        orderMenu.classList.toggle('show');
    });

    // Close order menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!orderMenu.contains(e.target) && !orderButton.contains(e.target)) {
            orderMenu.classList.remove('show');
        }
    });

    // Handle order option selection
    orderOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const orderValue = this.getAttribute('data-order');
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('order', orderValue);
            window.location.href = currentUrl.toString();
        });
    });
});