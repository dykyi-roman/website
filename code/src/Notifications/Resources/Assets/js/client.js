document.addEventListener('DOMContentLoaded', () => {
    // Dynamically determine WebSocket connection parameters
    const socketConfig = {
        protocol: window.location.protocol === 'https:' ? 'wss' : 'ws',
        host: window.location.host,
        path: '/ws'
    };

    let socket = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;
    const RECONNECT_TIMEOUT = 3000; // 3 seconds

    function createWebSocket() {
        const url = `${socketConfig.protocol}://${socketConfig.host}${socketConfig.path}`
        
        try {
            socket = new WebSocket(url);
            socket.onopen = function(event) {
                reconnectAttempts = 0;

                // Get user ID and authenticate
                const userId = getUserId();
                if (userId) {
                    authenticateUser(userId);
                } else {
                    console.error('No user ID found in meta tag');
                }
            };

            socket.onclose = function(event) {
                // Attempt to reconnect if not max attempts
                if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                    reconnectAttempts++;
                    console.log(`Attempting to reconnect (${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})...`);
                    setTimeout(createWebSocket, RECONNECT_TIMEOUT);
                }
            };

            socket.onerror = function(error) {
                console.error('WebSocket error:', error);
            };

            socket.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    console.log('Received message:', data);

                    switch (data.type) {
                        case 'auth_success':
                            console.log('Successfully authenticated:', data);
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
            };

        } catch (error) {
            console.error('Error creating WebSocket:', error);
        }
    }

    function authenticateUser(userId) {
        if (socket && socket.readyState === WebSocket.OPEN) {
            const authMessage = {
                type: 'authenticate',
                userId: userId,
                timestamp: new Date().toISOString()
            };
            
            console.log('Sending authentication request:', authMessage);
            socket.send(JSON.stringify(authMessage));
        } else {
            console.error('Cannot authenticate: WebSocket is not open');
        }
    }

    function getUserId() {
        const userIdMeta = document.querySelector('meta[name="user-id"]');
        if (!userIdMeta) {
            console.error('User ID meta tag not found');
            return null;
        }
        
        const userId = userIdMeta.getAttribute('content');
        if (!userId) {
            console.error('User ID meta tag is empty');
            return null;
        }
        
        return userId;
    }

    function updateNotificationBadge() {
        const badge = document.querySelector('.notifications-button .badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'block';
        }
    }

    function displayNotification(notification) {
        const notificationsContainer = document.getElementById('notifications');
        if (notificationsContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.className = 'notification-toast alert alert-' + (notification.type || 'info');
            
            const icon = getNotificationIcon(notification.type);
            notificationElement.innerHTML = `
                <div class="notification-content">
                    <div class="notification-header">
                        <i class="fas ${icon} me-2"></i>
                        <strong class="notification-title">${notification.title || ''}</strong>
                        <button type="button" class="notification-close" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="notification-body">
                        ${notification.message || ''}
                    </div>
                </div>
            `;
            
            // Add click event listener for close button
            const closeButton = notificationElement.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                notificationElement.remove();
            });
            
            notificationsContainer.appendChild(notificationElement);
            
            // Update the notification badge
            updateNotificationBadge();
            
            // Auto-remove notification after 5 seconds
            setTimeout(() => {
                if (notificationElement.parentElement) {
                    notificationElement.remove();
                }
            }, 5000);
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

    // Initial WebSocket connection
    createWebSocket();
});