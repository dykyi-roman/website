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
            <span class="notification-date" title="${formatDate(notification.createdAt, true)}">${formatDate(notification.createdAt)}</span>
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

    function formatDate(dateString, includeTime = false) {
        const date = new Date(dateString);
        if (includeTime) {
            return date.toLocaleString('en-GB', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        return date.toLocaleDateString('en-GB', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        });
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
                // Add removing class to trigger animation
                notificationElement.classList.add('removing');
                
                // Wait for animation to complete before removing element
                setTimeout(() => {
                    notificationElement.remove();
                    if (!notificationElement.classList.contains('read')) {
                        decrementNotificationCount();
                    }
                    showEmptyState();
                }, 300); // Match the CSS transition duration
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
        const badge = document.querySelector('.notifications-button .badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
                badge.textContent = '';
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
        initializeNotificationsMenu();
    }

    function initializeNotificationsMenu() {
        const menuButton = document.querySelector('.notifications-menu-button');
        const menu = document.querySelector('.notifications-menu');

        if (!menuButton || !menu) return;

        // Toggle menu on button click
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !menuButton.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // Handle menu item clicks
        menu.addEventListener('click', function(e) {
            const menuItem = e.target.closest('.notifications-menu-item');
            if (!menuItem) return;

            const action = menuItem.dataset.action;
            menu.classList.remove('show');

            if (action === 'read-all') {
                markAllAsRead();
            } else if (action === 'clear-all') {
                clearAllNotifications();
            }
        });
    }

    function markAllAsRead() {
        fetch('/api/v1/notifications', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                });
                updateNotificationBadge(0);
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }

    function clearAllNotifications() {
        fetch('/api/v1/notifications', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationsSection = document.querySelector('.notifications-section');
                if (notificationsSection) {
                    notificationsSection.querySelectorAll('.notification-item').forEach(item => {
                        item.remove();
                    });
                    showEmptyState();
                }
                updateNotificationBadge(0);
            }
        })
        .catch(error => console.error('Error clearing all notifications:', error));
    }
})();