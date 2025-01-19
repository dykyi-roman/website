document.addEventListener('DOMContentLoaded', function() {
    fetchNotificationCount();
    initializeNotificationHandlers();
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

function initializeNotificationHandlers() {
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        // Handle notification click
        notification.addEventListener('click', function(event) {
            // Don't mark as read if clicking the close button
            if (event.target.closest('.notification-close')) {
                return;
            }
            
            const notificationId = this.dataset.notificationId;
            if (this.classList.contains('unread')) {
                markAsRead(notificationId, this);
            }
        });

        // Handle close button click
        const closeButton = notification.querySelector('.notification-close');
        if (closeButton) {
            closeButton.addEventListener('click', function(event) {
                event.stopPropagation();
                const notificationId = this.closest('.notification-item').dataset.notificationId;
                removeNotification(notificationId, this.closest('.notification-item'));
            });
        }
    });
}

function markAsRead(notificationId, notificationElement) {
    if (!notificationId) return;

    fetch(`/api/v1/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationElement.classList.remove('unread');
            notificationElement.classList.add('read');
            
            // Update the unread count
            fetchNotificationCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function removeNotification(notificationId, notificationElement) {
    if (!notificationId) return;

    fetch(`/api/v1/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            notificationElement.style.opacity = '0';
            notificationElement.style.transform = 'translateX(-100%)';
            
            // Remove the element after animation
            setTimeout(() => {
                notificationElement.remove();
                // Update the unread count
                fetchNotificationCount();
            }, 300);
        }
    })
    .catch(error => {
        console.error('Error removing notification:', error);
    });
}