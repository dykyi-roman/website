document.addEventListener('DOMContentLoaded', function() {
    let currentShareUrl = '';

    // Function to properly combine base URL and path
    function buildUrl(base, path) {
        // Remove trailing slash from base URL
        base = base.replace(/\/$/, '');
        // Remove leading slash from path
        path = path.replace(/^\//, '');
        // Combine with a single slash
        return `${base}/${path}`;
    }

    // Share button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-share')) {
            const shareButton = e.target.closest('.btn-share');
            const itemCard = shareButton.closest('.item-card');
            const cardTitleLink = itemCard.querySelector('.card-title a');
            const itemPath = cardTitleLink ? cardTitleLink.getAttribute('href') : '';
            
            if (itemPath) {
                currentShareUrl = buildUrl(window.location.href, itemPath);
            } else {
                currentShareUrl = window.location.href;
            }
            
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            shareModal.show();
        }
    });

    // Copy link button handler
    document.querySelector('.share-button.copy-link').addEventListener('click', function() {
        navigator.clipboard.writeText(currentShareUrl).then(() => {
            // Change button text temporarily to show success
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });

    // Email share handler
    document.querySelector('.share-button.email').addEventListener('click', function() {
        const subject = encodeURIComponent('Check out this service');
        const body = encodeURIComponent(`I found this interesting service: ${currentShareUrl}`);
        window.open(`mailto:?subject=${subject}&body=${body}`);
    });

    // Twitter share handler
    document.querySelector('.share-button.twitter').addEventListener('click', function() {
        const text = encodeURIComponent('Check out this service:');
        window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(currentShareUrl)}`);
    });

    // WhatsApp share handler
    document.querySelector('.share-button.whatsapp').addEventListener('click', function() {
        window.open(`https://wa.me/?text=${encodeURIComponent(currentShareUrl)}`);
    });

    // Messenger share handler
    document.querySelector('.share-button.messenger').addEventListener('click', function() {
        window.open(`https://www.facebook.com/dialog/send?link=${encodeURIComponent(currentShareUrl)}&app_id=YOUR_FB_APP_ID`);
    });

    // Facebook share handler
    document.querySelector('.share-button.facebook').addEventListener('click', function() {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentShareUrl)}`);
    });
});
