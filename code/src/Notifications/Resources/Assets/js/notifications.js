(function() {
    // Track initialization
    let isInitialized = false;

    document.addEventListener('DOMContentLoaded', function() {
        if (isInitialized) {
            return;
        }
        fetchNotificationCount();
        initializeNotificationHandlers();
        isInitialized = true;
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

    function decrementNotificationCount() {
        const notificationIcon = document.querySelector('.notifications-button .fa-bell');
        if (!notificationIcon) return;

        const existingBadge = notificationIcon.querySelector('.badge');
        if (!existingBadge) return;

        const currentCount = parseInt(existingBadge.textContent);
        if (isNaN(currentCount)) return;

        const newCount = Math.max(0, currentCount - 1);
        
        if (newCount > 0) {
            existingBadge.innerHTML = `${newCount}<span class="visually-hidden">unread notifications</span>`;
        } else {
            existingBadge.remove();
        }
    }

    function initializeNotificationHandlers() {
        const notifications = document.querySelectorAll('.notification-item');
        
        notifications.forEach((notification) => {
            if (notification.dataset.handlersAttached === 'true') {
                return;
            }
            
            // Handle notification click
            notification.addEventListener('click', function(event) {
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
                    const notificationItem = this.closest('.notification-item');
                    const notificationId = notificationItem.dataset.notificationId;
                    removeNotification(notificationId, notificationItem);
                });
            }
            
            notification.dataset.handlersAttached = 'true';
        });
    }

    function markAsRead(notificationId, notificationElement) {
        if (!notificationId) return Promise.reject('No notification ID');

        notificationElement.style.pointerEvents = 'none';

        return fetch(`/api/v1/notifications/${notificationId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationElement.classList.remove('unread');
                void notificationElement.offsetWidth;
                notificationElement.classList.add('read');
                decrementNotificationCount();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        })
        .finally(() => {
            notificationElement.style.pointerEvents = '';
        });
    }

    function removeNotification(notificationId, notificationElement) {
        if (!notificationId) return Promise.reject('No notification ID');

        notificationElement.style.pointerEvents = 'none';

        return fetch(`/api/v1/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationElement.style.opacity = '0';
                notificationElement.style.transform = 'translateX(-100%)';
                
                const wasUnread = notificationElement.classList.contains('unread');
                
                setTimeout(() => {
                    notificationElement.remove();
                    if (wasUnread) {
                        decrementNotificationCount();
                    }
                }, 300);
            }
        })
        .catch(error => {
            console.error('Error removing notification:', error);
        })
        .finally(() => {
            notificationElement.style.pointerEvents = '';
        });
    }
})();