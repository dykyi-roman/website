// Facebook App ID for sharing functionality
const FB_APP_ID = 'YOUR_FB_APP_ID';

document.addEventListener('DOMContentLoaded', async function() {
    // Get current language or default to English
    const currentLang = CookieService.get('locale') || 'en';
    const t = await loadTranslations(currentLang);

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

    // Action button handlers
    document.querySelectorAll('.popup-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            
            switch(action) {
                case 'copy-link':
                    navigator.clipboard.writeText(currentShareUrl).then(() => {
                        // Change button text temporarily to show success
                        const originalText = this.innerHTML;
                        this.innerHTML = `<i class="fas fa-check"></i><span>${t.share_link_copied || 'Copied!'}</span>`;
                        setTimeout(() => {
                            this.innerHTML = originalText;
                        }, 1000);
                    });
                    break;
                    
                case 'email':
                    const subject = encodeURIComponent(t.share_email_subject || 'Check out this service');
                    const body = encodeURIComponent(`${t.share_email_body || 'I found this interesting service: '}${currentShareUrl}`);
                    window.open(`mailto:?subject=${subject}&body=${body}`);
                    break;
                    
                case 'twitter':
                    const text = encodeURIComponent('Check out this service:');
                    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(currentShareUrl)}`);
                    break;
                    
                case 'whatsapp':
                    window.open(`https://wa.me/?text=${encodeURIComponent(currentShareUrl)}`);
                    break;
                    
                case 'messenger':
                    window.open(`https://www.facebook.com/dialog/send?link=${encodeURIComponent(currentShareUrl)}&app_id=${FB_APP_ID}`);
                    break;
                    
                case 'facebook':
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentShareUrl)}`);
                    break;
            }
        });
    });
});
