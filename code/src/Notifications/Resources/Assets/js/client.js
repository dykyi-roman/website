class NotificationClient {
    constructor() {
        this.eventSource = null;
        this.badgeCounter = 0;
        this.initialize();
    }

    initialize() {
        const sseUrl = 'https://127.0.0.1:1001/sse';
        console.log('Initializing SSE connection to:', sseUrl);
        
        if (this.eventSource) {
            console.log('Closing existing connection');
            this.eventSource.close();
        }

        this.eventSource = new EventSource(sseUrl, {
            withCredentials: true
        });
        
        this.eventSource.onmessage = (event) => {
            console.log('Raw SSE message:', event.data);
            this.handleMessage(event);
        };
        
        this.eventSource.onerror = (error) => {
            console.error('SSE Error details:', {
                readyState: this.eventSource.readyState,
                url: this.eventSource.url,
                error
            });
            this.handleError(error);
        };
        
        this.eventSource.onopen = () => {
            console.log('SSE connection established, readyState:', this.eventSource.readyState);
        };
        
        this.eventSource.addEventListener('notification', (event) => {
            console.log('Notification received:', event.data);
            this.handleNotification(event);
        });
    }

    handleMessage(event) {
        console.log('Received message:', event.data);
    }

    handleNotification(event) {
        const notification = JSON.parse(event.data);
        this.showToast(notification);
        this.updateBadgeCounter();
    }

    handleError(error) {
        console.error('SSE Error:', error);
        const state = this.eventSource ? this.eventSource.readyState : 'CLOSED';
        console.log('EventSource state:', state);
        
        // Close the connection if it's in error state
        if (this.eventSource && this.eventSource.readyState === EventSource.CLOSED) {
            this.eventSource.close();
            // Attempt to reconnect after 3 seconds
            setTimeout(() => this.initialize(), 3000);
        }
    }

    showToast(notification) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.classList.add(`notification-type-${notification.type.toLowerCase()}`);
        
        toast.innerHTML = `
            <div class="notification-content">
                <div class="notification-message">${notification.message}</div>
                <div class="notification-timestamp">${notification.timestamp}</div>
            </div>
        `;

        // Add to document
        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    updateBadgeCounter() {
        this.badgeCounter++;
        const badge = document.querySelector('.notifications-button .badge');
        if (badge) {
            badge.textContent = this.badgeCounter;
            badge.style.display = this.badgeCounter > 0 ? 'block' : 'none';
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Add styles
const style = document.createElement('style');
style.textContent = `
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    }

    .notification-toast.fade-out {
        animation: fadeOut 0.3s ease-out;
    }

    .notification-type-info {
        border-left: 4px solid #2196F3;
    }

    .notification-type-success {
        border-left: 4px solid #4CAF50;
    }

    .notification-type-warning {
        border-left: 4px solid #FFC107;
    }

    .notification-type-error {
        border-left: 4px solid #F44336;
    }

    .notification-content {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .notification-message {
        font-size: 14px;
        color: #333;
    }

    .notification-timestamp {
        font-size: 12px;
        color: #666;
    }

    .notifications-button .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #F44336;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        display: none;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;

document.head.appendChild(style);

// Initialize the notification client
const notificationClient = new NotificationClient();