async function toggleFavorite(button) {
    console.log('Module::Dashboard::Created');

    const serviceId = button.getAttribute('data-item-id');
    const isFavorite = button.classList.contains('active');
    
    try {
        const response = await fetch('/api/partner/toggle-favorite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                serviceId: serviceId,
                favorite: !isFavorite
            })
        });

        if (response.ok) {
            button.classList.toggle('active');
            const icon = button.querySelector('i');
            
            if (button.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        } else {
            console.error('Failed to update favorite status');
        }
    } catch (error) {
        console.error('Error updating favorite status:', error);
    }
}

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