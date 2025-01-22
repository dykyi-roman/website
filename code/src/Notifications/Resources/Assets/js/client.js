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
                            displayNotification(data);
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

    function displayNotification(notification) {
        const notificationContainer = document.getElementById('notifications');
        if (notificationContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.className = 'notification';
            
            // Create notification content with flex layout
            notificationElement.style.display = 'flex';
            notificationElement.style.alignItems = 'flex-start';
            notificationElement.style.gap = '10px';
            
            // Left type icon
            const typeIcon = document.createElement('i');
            typeIcon.className = `fas ${getNotificationIcon(notification.type)}`;
            
            // Content container for title and message
            const contentContainer = document.createElement('div');
            contentContainer.style.flex = '1';
            
            // Title
            const titleElement = document.createElement('div');
            titleElement.style.fontWeight = 'bold';
            titleElement.textContent = notification.title;
            
            // Message
            const messageElement = document.createElement('div');
            messageElement.textContent = notification.message.length > 100 
                ? notification.message.substring(0, 100) + '...' 
                : notification.message;
            
            contentContainer.appendChild(titleElement);
            contentContainer.appendChild(messageElement);
            
            // Append elements
            notificationElement.appendChild(typeIcon);
            notificationElement.appendChild(contentContainer);
            
            // Right icon if provided
            if (notification.icon) {
                const rightIcon = document.createElement('i');
                rightIcon.className = `fas ${notification.icon}`;
                notificationElement.appendChild(rightIcon);
            }
            
            notificationContainer.appendChild(notificationElement);

            // Auto-remove notification after 5 seconds
            setTimeout(() => {
                notificationElement.remove();
            }, 5000);
        }
    }

    function getNotificationIcon(type) {
        // Add logic to return the correct icon class based on the notification type
        // For example:
        switch (type) {
            case 'success':
                return 'fa-check-circle';
            case 'error':
                return 'fa-exclamation-circle';
            case 'info':
                return 'fa-info-circle';
            default:
                return 'fa-bell';
        }
    }

    // Initial WebSocket connection
    createWebSocket();
});