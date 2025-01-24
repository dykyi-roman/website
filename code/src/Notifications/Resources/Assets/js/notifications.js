(() => {
    // Configuration
    const CONFIG = {
        PAGE_SIZE: 20,
        WEBSOCKET: {
            MAX_RECONNECT_ATTEMPTS: 5,
            RECONNECT_TIMEOUT: 3000
        }
    };

    // State management
    const state = {
        currentPage: 1,
        isLoading: false,
        hasMoreNotifications: true,
        isInitialized: false,
        translations: {},
        socket: null,
        reconnectAttempts: 0
    };

    // WebSocket Management
    const socketConfig = {
        protocol: window.location.protocol === 'https:' ? 'wss' : 'ws',
        host: window.location.host,
        path: '/ws'
    };

    function createWebSocket() {
        const url = `${socketConfig.protocol}://${socketConfig.host}${socketConfig.path}`;
        
        try {
            state.socket = new WebSocket(url);
            
            state.socket.onopen = function(event) {
                const wasReconnecting = state.reconnectAttempts > 0;
                state.reconnectAttempts = 0;
                const userId = getUserId();
                if (userId) {
                    authenticateUser(userId);
                    if (wasReconnecting) {
                        fetchNotificationCount();
                    }
                } else {
                    console.error('No user ID found in meta tag');
                }
            };

            state.socket.onclose = function(event) {
                if (state.reconnectAttempts < CONFIG.WEBSOCKET.MAX_RECONNECT_ATTEMPTS) {
                    state.reconnectAttempts++;
                    console.log(`Attempting to reconnect (${state.reconnectAttempts}/${CONFIG.WEBSOCKET.MAX_RECONNECT_ATTEMPTS})...`);
                    setTimeout(createWebSocket, CONFIG.WEBSOCKET.RECONNECT_TIMEOUT);
                }
            };

            state.socket.onerror = function(error) {
                console.error('WebSocket error:', error);
            };

            state.socket.onmessage = handleWebSocketMessage;

        } catch (error) {
            console.error('Error creating WebSocket:', error);
        }
    }

    function handleWebSocketMessage(event) {
        try {
            const data = JSON.parse(event.data);
            switch (data.type) {
                case 'auth_success':
                    console.log('Successfully authenticated!');
                    break;
                case 'error':
                    console.error('Server error:', data.message);
                    break;
                case 'notification':
                    displayNotification(data.message);
                    break;
                default:
                    console.log('Unhandled message type:', data.type);
            }
        } catch (error) {
            console.error('Error processing message:', error);
        }
    }

    function displayNotification(notification) {
        // Increment the notification badge count first
        incrementNotificationCount();

        // Display toast notification
        const notificationsContainer = document.getElementById('notifications');
        if (notificationsContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.className = 'notification-toast alert alert-' + (notification.type || 'info');

            const iconHtml = notification.icon
                ? `<div class="notification-custom-icon"><img src="${notification.icon}" alt=""></div>`
                : '';

            const icon = getNotificationIcon(notification.type);
            notificationElement.innerHTML = `
                <div class="notification-content">
                    <div class="notification-header">
                        <i class="fas ${icon} me-2"></i>
                        <strong class="notification-title">${notification.title || ''}</strong>
                        <button type="button" class="notification-close" aria-label="X">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="notification-body">
                        ${notification.message.length > 100 ? notification.message.substring(0, 100) + '...' : notification.message}
                    </div>
                    ${iconHtml}
                </div>
            `;
            
            // Add close button functionality
            const closeButton = notificationElement.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                removeToastNotification(notificationElement);
            });
            
            notificationsContainer.appendChild(notificationElement);
            
            // Auto-remove with animation
            setTimeout(() => {
                removeToastNotification(notificationElement);
            }, 5000);
        }

        // Update notifications page if we're on it
        if (window.location.pathname === '/notifications') {
            const timestamp = Date.now(); // Current timestamp in milliseconds
            
            // Create the new notification element
            const notificationData = {
                id: notification.id,
                type: notification.type,
                title: notification.title,
                message: notification.message,
                icon: notification.icon,
                link: notification.link,
                createdAt: timestamp,
                is_read: false
            };

            const newNotificationElement = createNotificationElement(notificationData);

            // Add to today's notifications group
            const todayGroup = document.getElementById('today-notifications');
            if (todayGroup) {
                // Insert after the title
                const titleElement = todayGroup.querySelector('.notification-group-title');
                if (titleElement) {
                    if (titleElement.nextSibling) {
                        todayGroup.insertBefore(newNotificationElement, titleElement.nextSibling);
                    } else {
                        todayGroup.appendChild(newNotificationElement);
                    }
                } else {
                    todayGroup.appendChild(newNotificationElement);
                }

                // Initialize the notification element
                initializeNotificationElement(newNotificationElement);

                // Hide empty state message if it exists
                const emptyMessage = document.querySelector('.no-notifications-message');
                if (emptyMessage) {
                    emptyMessage.style.display = 'none';
                }

                // Show today's group if it was hidden
                todayGroup.style.display = 'block';
            }
        }
    }

    function removeToastNotification(notificationElement) {
        // Add closing class for animation
        notificationElement.classList.add('closing');
        
        // Remove element after animation completes
        setTimeout(() => {
            if (notificationElement.parentElement) {
                notificationElement.parentElement.removeChild(notificationElement);
            }
        }, 500); // Match the CSS transition duration
    }

    function createNotificationElement(notification) {
        const div = document.createElement('div');
        // Проверяем readAt для определения статуса прочтения
        const isRead = notification.readAt;
        div.className = `notification-item ${isRead ? 'read' : 'unread'}`;
        div.dataset.notificationId = notification.id;

        const date = new Date(notification.createdAt);
        const timestamp = date.getTime();

        const iconHtml = notification.icon 
            ? `<div class="notification-custom-icon"><img src="${notification.icon}" alt=""></div>` 
            : '';

        div.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-details">
                <h3>${notification.title}</h3>
                <p>${notification.message.length > 500 ? notification.message.substring(0, 500) + '...' : notification.message}</p>
            </div>
            ${iconHtml}
            <span class="notification-date" data-timestamp="${timestamp}" title="${date.toLocaleString()}">${getTimeAgo(date)}</span>
            <button class="notification-close" aria-label="X">
                <i class="fas fa-times"></i>
            </button>
        `;

        return div;
    }

    function authenticateUser(userId) {
        if (state.socket?.readyState === WebSocket.OPEN) {
            const authMessage = {
                type: 'authenticate',
                userId: userId,
                timestamp: new Date().toISOString()
            };
            state.socket.send(JSON.stringify(authMessage));
        } else {
            console.error('Cannot authenticate: WebSocket is not open');
        }
    }

    function getUserId() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        return userIdMeta?.getAttribute('content') || null;
    }

    // Notification Display and Management
    async function initializeTranslations() {
        const lang = document.documentElement.lang || 'en';
        state.translations = await window.loadTranslations(lang);
    }

    function getTranslation(path) {
        return path.split('.').reduce((obj, key) => obj && obj[key], state.translations) || path;
    }

    function loadNotifications(page = 1) {
        if (state.isLoading || (!state.hasMoreNotifications && page > 1)) return;

        state.isLoading = true;
        const notificationsSection = document.querySelector('.notifications-section');
        const loadMoreBtn = document.querySelector('.load-more-btn');
        const noNotificationsMessage = document.querySelector('.no-notifications-message');

        fetch(`/api/v1/notifications?page=${page}&limit=${CONFIG.PAGE_SIZE}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (page === 1) {
                clearNotificationGroups();
            }

            if (data.data?.items) {
                handleNotificationsResponse(data, page);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        })
        .finally(() => {
            state.isLoading = false;
        });
    }

    function handleNotificationsResponse(data, page) {
        const notifications = data.data.items;
        const noNotificationsMessage = document.querySelector('.no-notifications-message');
        const loadMoreBtn = document.querySelector('.load-more-btn');

        if (notifications.length === 0 && page === 1) {
            showEmptyState(noNotificationsMessage, loadMoreBtn);
            return;
        }

        notifications.forEach(notification => {
            const notificationElement = createNotificationElement({
                id: notification.id,
                type: notification.type,
                title: notification.title,
                message: notification.message,
                icon: notification.icon, // Add icon property
                createdAt: notification.createdAt,
                readAt: notification.readAt
            });
            
            const groupId = getNotificationGroup(notification.createdAt);
            const group = document.getElementById(groupId);

            if (group) {
                group.appendChild(notificationElement);
                group.style.display = 'block';
                initializeNotificationElement(notificationElement);
            }
        });

        updateGroupsVisibility();
        state.hasMoreNotifications = page < data.data.total_pages;
        state.currentPage = page;
        
        loadMoreBtn.style.display = state.hasMoreNotifications ? 'inline-block' : 'none';
        noNotificationsMessage.style.display = 'none';
    }

    function clearNotificationGroups() {
        document.querySelectorAll('.notification-group').forEach(group => {
            const title = group.querySelector('.notification-group-title');
            while (group.lastChild !== title) {
                group.removeChild(group.lastChild);
            }
        });
    }

    function showEmptyState(noNotificationsMessage, loadMoreBtn) {
        noNotificationsMessage.style.display = 'block';
        loadMoreBtn.style.display = 'none';
        document.querySelectorAll('.notification-group').forEach(group => {
            group.style.display = 'none';
        });
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

    function getPluralForm(number) {
        if (number % 10 === 1 && number % 100 !== 11) {
            return 'one';
        } else if ([2, 3, 4].includes(number % 10) && ![12, 13, 14].includes(number % 100)) {
            return 'few';
        } else {
            return 'many';
        }
    }

    function getTimeAgo(date) {
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);

        // Меньше минуты
        if (diff < 60) {
            return getTranslation('notifications.time.just_now');
        }

        // Меньше часа (в минутах)
        if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            const form = getPluralForm(minutes);
            return `${minutes} ${getTranslation(`notifications.time.minute.${form}`)}`;
        }

        // Меньше суток (в часах)
        if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            const form = getPluralForm(hours);
            return `${hours} ${getTranslation(`notifications.time.hour.${form}`)}`;
        }

        // Меньше 30 дней (в днях)
        if (diff < 2592000) {
            const days = Math.floor(diff / 86400);
            const form = getPluralForm(days);
            return `${days} ${getTranslation(`notifications.time.day.${form}`)}`;
        }

        // Меньше года (в месяцах)
        if (diff < 31536000) {
            const months = Math.floor(diff / 2592000);
            const form = getPluralForm(months);
            return `${months} ${getTranslation(`notifications.time.month.${form}`)}`;
        }

        // Больше года (в годах)
        const years = Math.floor(diff / 31536000);
        const form = getPluralForm(years);
        return `${years} ${getTranslation(`notifications.time.year.${form}`)}`;
    }

    function updateAllNotificationTimes() {
        document.querySelectorAll('.notification-date[data-timestamp]').forEach(dateSpan => {
            const timestamp = parseInt(dateSpan.dataset.timestamp);
            if (!isNaN(timestamp)) {
                const date = new Date(timestamp);
                dateSpan.textContent = getTimeAgo(date);
            }
        });
    }

    function getNotificationGroup(createdAt) {
        const now = new Date();
        const notificationDate = new Date(createdAt);

        // Reset hours to compare just the dates
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const notifDay = new Date(notificationDate.getFullYear(), notificationDate.getMonth(), notificationDate.getDate());

        // Calculate the difference in days
        const diffTime = today.getTime() - notifDay.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return 'today-notifications';
        } else if (diffDays <= 7) {
            return 'week-notifications';
        } else {
            return 'earlier-notifications';
        }
    }

    function updateGroupsVisibility() {
        let hasVisibleGroups = false;
        document.querySelectorAll('.notification-group').forEach(group => {
            const notifications = group.querySelectorAll('.notification-item');
            const hasNotifications = notifications.length > 0;
            group.style.display = hasNotifications ? 'block' : 'none';
            if (hasNotifications) {
                hasVisibleGroups = true;
            }
        });

        const noNotificationsMessage = document.querySelector('.no-notifications-message');
        if (noNotificationsMessage) {
            noNotificationsMessage.style.display = hasVisibleGroups ? 'none' : 'block';
        }

        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn && !hasVisibleGroups) {
            loadMoreBtn.style.display = 'none';
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
        if (!notificationId || !notificationElement) return;

        fetch(`/api/v1/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                // Add fade out animation
                notificationElement.style.opacity = '0';
                notificationElement.style.transform = 'translateX(20px)';

                setTimeout(() => {
                    // Only decrement count if notification was unread
                    const isUnread = !notificationElement.classList.contains('read');
                    if (isUnread) {
                        decrementNotificationCount();
                    }
                    
                    notificationElement.remove();
                    // Update visibility of all groups after removing notification
                    updateGroupsVisibility();
                }, 300);
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
            if (typeof count === 'number') {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'block' : 'none';
            }
        }
    }

    function incrementNotificationCount() {
        const badge = document.querySelector('.notifications-button .badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'block';
        }
    }

    function decrementNotificationCount() {
        const badge = document.querySelector('.notifications-button .badge');
        if (badge && badge.style.display !== 'none') {
            const currentCount = parseInt(badge.textContent);
            if (currentCount > 1) {
                badge.textContent = currentCount - 1;
            } else {
                badge.textContent = '0';
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
            method: 'PUT',
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
        .then(response => {
            if (response.ok) {
                // Анимация удаления для всех уведомлений
                const notifications = document.querySelectorAll('.notification-item');
                notifications.forEach(notification => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(20px)';
                });

                // Удаляем все уведомления после анимации
                setTimeout(() => {
                    notifications.forEach(notification => notification.remove());
                    // Обновляем счетчик уведомлений
                    updateNotificationBadge(0);
                    // Обновляем видимость групп
                    updateGroupsVisibility();
                }, 300);
            }
        })
        .catch(error => {
            console.error('Error clearing all notifications:', error);
        });
    }

    function initializeLoadMoreButton() {
        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                loadNotifications(state.currentPage + 1);
            });
        }
    }

    function initializeTimeUpdates() {
        // Обновляем время сразу при загрузке
        updateAllNotificationTimes();
        
        // Устанавливаем интервал обновления каждую минуту
        setInterval(updateAllNotificationTimes, 60000);
    }

    // Event Handlers and Initialization
    document.addEventListener('DOMContentLoaded', async function() {
        if (state.isInitialized) return;

        await initializeTranslations();

        if (window.location.pathname === '/notifications') {
            loadNotifications();
            initializeTimeUpdates();
        }

        fetchNotificationCount();
        initializeNotificationHandlers();
        initializeLoadMoreButton();
        createWebSocket();
        
        state.isInitialized = true;
    });
})();
