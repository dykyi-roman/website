(function() {
    // Configuration
    const PAGE_SIZE = 20;
    let currentPage = 1;
    let isLoading = false;
    let hasMoreNotifications = true;
    let isInitialized = false;
    let translations = {};

    async function initializeTranslations() {
        const lang = document.documentElement.lang || 'en';
        translations = await window.loadTranslations(lang);
    }

    function getTranslation(path) {
        return path.split('.').reduce((obj, key) => obj && obj[key], translations) || path;
    }

    document.addEventListener('DOMContentLoaded', async function() {
        if (isInitialized) {
            return;
        }

        // Load translations first
        await initializeTranslations();

        // Only load notifications if we're on the notifications page
        if (window.location.pathname === '/notifications') {
            loadNotifications();
            initializeTimeUpdates();
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
                // Clear all groups
                document.querySelectorAll('.notification-group').forEach(group => {
                    const title = group.querySelector('.notification-group-title');
                    // Remove all notifications but keep the title
                    while (group.lastChild !== title) {
                        group.removeChild(group.lastChild);
                    }
                });
            }

            if (data.data && Array.isArray(data.data.items)) {
                const notifications = data.data.items;

                if (notifications.length === 0 && page === 1) {
                    noNotificationsMessage.style.display = 'block';
                    loadMoreBtn.style.display = 'none';
                    document.querySelectorAll('.notification-group').forEach(group => {
                        group.style.display = 'none';
                    });
                    return;
                }

                notifications.forEach(notification => {
                    const notificationElement = createNotificationElement(notification);
                    const groupId = getNotificationGroup(notification.createdAt);
                    const group = document.getElementById(groupId);

                    if (group) {
                        group.appendChild(notificationElement);
                        group.style.display = 'block';
                    }

                    // Add click handlers to the new notification element
                    initializeNotificationElement(notificationElement);
                });

                updateGroupsVisibility();

                // Check if there are more pages
                hasMoreNotifications = page < data.data.total_pages;
                loadMoreBtn.style.display = hasMoreNotifications ? 'inline-block' : 'none';
                currentPage = page;
                noNotificationsMessage.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        })
        .finally(() => {
            isLoading = false;
        });
    }

    function updateGroupsVisibility() {
        let hasVisibleGroups = false;
        document.querySelectorAll('.notification-group').forEach(group => {
            // Проверяем, есть ли уведомления в группе (исключая заголовок)
            const notifications = group.querySelectorAll('.notification-item');
            const hasNotifications = notifications.length > 0;
            group.style.display = hasNotifications ? 'block' : 'none';
            if (hasNotifications) {
                hasVisibleGroups = true;
            }
        });

        // Показываем сообщение о пустом списке, если нет видимых групп
        const noNotificationsMessage = document.querySelector('.no-notifications-message');
        if (noNotificationsMessage) {
            noNotificationsMessage.style.display = hasVisibleGroups ? 'none' : 'block';
        }

        // Скрываем кнопку "Загрузить еще", если нет видимых групп
        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn && !hasVisibleGroups) {
            loadMoreBtn.style.display = 'none';
        }
    }

    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = `notification-item ${notification.readAt ? 'read' : 'unread'}`;
        div.dataset.notificationId = notification.id;

        const timestamp = new Date(notification.createdAt).getTime();

        div.innerHTML = `
            <div class="notification-icon">
                <i class="fas ${getNotificationIcon(notification.type)}"></i>
            </div>
            <div class="notification-details">
                <h3>${notification.title}</h3>
                <p>${notification.message}</p>
            </div>
            <span class="notification-date" data-timestamp="${timestamp}" title="${new Date(notification.createdAt).toLocaleString()}">${getTimeAgo(new Date(notification.createdAt))}</span>
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
        document.querySelectorAll('.notification-item').forEach(item => {
            const dateSpan = item.querySelector('.notification-date');
            if (dateSpan) {
                const timestamp = dateSpan.getAttribute('data-timestamp');
                if (timestamp) {
                    const date = new Date(parseInt(timestamp));
                    dateSpan.textContent = getTimeAgo(date);
                }
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
                    notificationElement.remove();
                    decrementNotificationCount();

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

    function updateGroupsVisibility() {
        let hasVisibleGroups = false;
        document.querySelectorAll('.notification-group').forEach(group => {
            // Проверяем, есть ли уведомления в группе (исключая заголовок)
            const notifications = group.querySelectorAll('.notification-item');
            const hasNotifications = notifications.length > 0;

            // Анимируем скрытие/показ группы
            if (hasNotifications) {
                group.style.display = 'block';
                group.style.opacity = '1';
                hasVisibleGroups = true;
            } else {
                group.style.opacity = '0';
                setTimeout(() => {
                    group.style.display = 'none';
                }, 300);
            }
        });

        // Показываем сообщение о пустом списке, если нет видимых групп
        const noNotificationsMessage = document.querySelector('.no-notifications-message');
        if (noNotificationsMessage) {
            if (!hasVisibleGroups) {
                noNotificationsMessage.style.display = 'block';
                noNotificationsMessage.style.opacity = '0';
                setTimeout(() => {
                    noNotificationsMessage.style.opacity = '1';
                }, 10);
            } else {
                noNotificationsMessage.style.opacity = '0';
                setTimeout(() => {
                    noNotificationsMessage.style.display = 'none';
                }, 300);
            }
        }

        // Скрываем кнопку "Загрузить еще", если нет видимых групп
        const loadMoreBtn = document.querySelector('.load-more-btn');
        if (loadMoreBtn) {
            if (!hasVisibleGroups) {
                loadMoreBtn.style.opacity = '0';
                setTimeout(() => {
                    loadMoreBtn.style.display = 'none';
                }, 300);
            } else {
                loadMoreBtn.style.display = 'block';
                loadMoreBtn.style.opacity = '1';
            }
        }
    }

    // Инициализация обновления времени
    let timeUpdateInterval;

    function initializeTimeUpdates() {
        // Обновляем время каждую минуту
        timeUpdateInterval = setInterval(updateAllNotificationTimes, 60000);

        // Останавливаем обновление, когда страница скрыта
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(timeUpdateInterval);
            } else {
                updateAllNotificationTimes();
                timeUpdateInterval = setInterval(updateAllNotificationTimes, 60000);
            }
        });
    }
})();