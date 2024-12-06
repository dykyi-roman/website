async function toggleFavorite(button) {
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

// Create scroll to top button
document.addEventListener('DOMContentLoaded', function() {
    // Create the button element
    const scrollButton = document.createElement('div');
    scrollButton.className = 'scroll-to-top';
    scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    document.body.appendChild(scrollButton);

    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollButton.classList.add('visible');
        } else {
            scrollButton.classList.remove('visible');
        }
    });

    // Smooth scroll to top when button is clicked
    scrollButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
