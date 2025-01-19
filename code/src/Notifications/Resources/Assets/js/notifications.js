(function() {
    // Configuration
    const PAGE_SIZE = 20;
    let currentPage = 1;
    let isLoading = false;
    let hasMoreNotifications = true;
    let isInitialized = false;

    document.addEventListener('DOMContentLoaded', function() {
        if (isInitialized) {
            return;
        }

        // Only load notifications if we're on the notifications page
        if (window.location.pathname === '/notifications') {
            loadNotifications();
        }
        
        fetchNotificationCount();
        initializeNotificationHandlers();
        initializeLoadMoreButton();
        isInitialized = true;
    });

    function loadNotifications(page = 1) {
        if (isLoading || (!hasMoreNotifications && page > 1)) return;

        isLoading = true;
        const notificationsSection = document.querySelector('.notifications-section');
        const loadMoreBtn = document.querySelector('.load-more-btn');
        const noNotificationsMessage = document.querySelector('.no-notifications-message');

        fetch(`/api/v1/notifications?page=${page}&limit=${PAGE_SIZE}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (page === 1) {
                notificationsSection.querySelectorAll('.notification-item').forEach(item => item.remove());
            }

            if (data.data && Array.isArray(data.data.items)) {
                const notifications = data.data.items;
                
                if (notifications.length === 0 && page === 1) {
                    noNotificationsMessage.style.display = 'block';
                    loadMoreBtn.style.display = 'none';
                    return;
                }

                notifications.forEach(notification => {
                    const notificationElement = createNotificationElement(notification);
                    notificationsSection.insertBefore(notificationElement, noNotificationsMessage);
                    // Add click handlers to the new notification element
                    initializeNotificationElement(notificationElement);
                });

                // Check if there are more pages
                hasMoreNotifications = page < data.data.total_pages;
                loadMoreBtn.style.display = hasMoreNotifications ? 'inline-block' : 'none';
                currentPage = page;
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        })
        .finally(() => {
            isLoading = false;
        });
    }

    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${notification.readAt ? 'read' : 'unread'}`;
        div.dataset.notificationId = notification.id;

        div.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-details">
                <h3>${notification.title}</h3>
                <p>${notification.message}</p>
            </div>
            <span class="notification-date">${formatDate(notification.createdAt)}</span>
            <button class="notification-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;

        return div;
    }

    function initializeNotificationElement(element) {
        // Add click handler for the entire notification
        element.addEventListener('click', function(event) {
            // Ignore clicks on the close button
            if (!event.target.closest('.notification-close')) {
                const notificationId = this.dataset.notificationId;
                markAsRead(notificationId, this);
            }
        });

        // Add click handler for the close button
        const closeButton = element.querySelector('.notification-close');
        if (closeButton) {
            closeButton.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevent notification click event
                const notificationItem = this.closest('.notification-item');
                const notificationId = notificationItem.dataset.notificationId;
                removeNotification(notificationId, notificationItem);
            });
        }
    }

    function getNotificationIcon(type) {
        const icons = {
            'personal': 'fa-user',
            'system': 'fa-cog',
            'information': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-exclamation-circle'
        };
        return icons[type] || 'fa-bell';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function initializeLoadMoreButton() {
        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                loadNotifications(currentPage + 1);
            });
        }
    }

    function showEmptyState() {
        const notificationsSection = document.querySelector('.notifications-section');
        const noNotificationsMessage = document.querySelector('.no-notifications-message');
        if (!notificationsSection) return;

        const remainingNotifications = notificationsSection.querySelectorAll('.notification-item');
        if (remainingNotifications.length === 0) {
            noNotificationsMessage.style.display = 'block';
            document.querySelector('.load-more-btn').style.display = 'none';
        }
    }

    function markAsRead(notificationId, notificationElement) {
        if (!notificationElement.classList.contains('read')) {
            fetch(`/api/v1/notifications/${notificationId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationElement.classList.add('read');
                    notificationElement.classList.remove('unread');
                    decrementNotificationCount();
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
    }

    function removeNotification(notificationId, notificationElement) {
        fetch(`/api/v1/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationElement.remove();
                if (!notificationElement.classList.contains('read')) {
                    decrementNotificationCount();
                }
                showEmptyState();
            }
        })
        .catch(error => {
            console.error('Error removing notification:', error);
        });
    }

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
        const badge = document.querySelector('.notifications-button .badge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    function decrementNotificationCount() {
        const badge = document.querySelector('.notifications-button .badge');
        if (badge && badge.style.display !== 'none') {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 1) {
                badge.textContent = currentCount - 1;
            } else {
                badge.style.display = 'none';
            }
        }
    }

    function initializeNotificationHandlers() {
        // This is now only used for initial setup of any static elements
        // Dynamic elements are handled by initializeNotificationElement
    }
})();