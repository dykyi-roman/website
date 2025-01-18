class NotificationManager {
    constructor() {
        this.hub = new URL('https://example.com/.well-known/mercure');
        this.notifications = [];
        this.unreadCount = 0;
    }

    async init() {
        // Подписываемся на уведомления
        const eventSource = new EventSource(this.hub);
        eventSource.onmessage = this.handleNotification.bind(this);

        // Загружаем начальное состояние
        await this.loadInitialState();

        // Обновляем UI
        this.updateUI();
    }

    async loadInitialState() {
        const response = await fetch('/api/notifications/count');
        const data = await response.json();
        this.unreadCount = data.count;

        // Загружаем последние уведомления
        await this.loadNotifications(1);
    }

    async loadNotifications(page) {
        const response = await fetch(`/api/notifications/list?page=${page}`);
        const data = await response.json();
        this.notifications = data.items;
        this.updateNotificationsList();
    }

    handleNotification(event) {
        const data = JSON.parse(event.data);

        // Показываем всплывающее уведомление
        this.showToast(data);

        // Обновляем счетчик
        this.unreadCount++;
        this.updateUI();

        // Добавляем в начало списка
        this.notifications.unshift(data);
        this.updateNotificationsList();
    }

    showToast(notification) {
        // Создаем и показываем toast
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <h4>${notification.title}</h4>
            <p>${notification.message}</p>
        `;

        document.body.appendChild(toast);

        // Удаляем через 5 секунд
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    updateUI() {
        // Обновляем счетчик в header
        const counter = document.querySelector('.notification-counter');
        counter.textContent = this.unreadCount;
        counter.style.display = this.unreadCount > 0 ? 'block' : 'none';
    }

    updateNotificationsList() {
        const container = document.querySelector('.notifications-list');
        container.innerHTML = this.notifications
            .map(n => `
                <div class="notification ${n.isRead ? 'read' : 'unread'}">
                    <h4>${n.title}</h4>
                    <p>${n.message}</p>
                    ${n.link ? `<a href="${n.link}">Подробнее</a>` : ''}
                    <button onclick="notificationManager.delete(${n.id})">×</button>
                </div>
            `)
            .join('');
    }
}