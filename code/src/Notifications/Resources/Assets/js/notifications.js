document.addEventListener('DOMContentLoaded', function() {
    fetchNotificationCount();
});

function fetchNotificationCount() {
    fetch('/api/v1/notifications?includeCount=true', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.data && typeof data.data.unreadCount !== 'undefined') {
                updateNotificationBadge(data.data.unreadCount);
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
}

function updateNotificationBadge(count) {
    const notificationIcon = document.querySelector('.notifications-button .fa-bell');
    if (!notificationIcon) return;

    // Remove existing badge if any
    const existingBadge = notificationIcon.querySelector('.badge');
    if (existingBadge) {
        existingBadge.remove();
    }

    // Only show badge if count is greater than 0
    if (count > 0) {
        const badge = document.createElement('span');
        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
        badge.innerHTML = `${count}<span class="visually-hidden">unread notifications</span>`;
        notificationIcon.appendChild(badge);
    }
}