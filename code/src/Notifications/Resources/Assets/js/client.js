document.addEventListener('DOMContentLoaded', () => {
    // Dynamically determine WebSocket connection parameters
    const socketConfig = {
        protocol: window.location.protocol === 'https:' ? 'wss' : 'ws',
        host: '127.0.0.1:1004',
        port: 1004,
        path: '/ws'
    };

    // Construct WebSocket URL dynamically
    function constructWebSocketUrl() {
        return `${socketConfig.protocol}://${socketConfig.host}${socketConfig.path}`;
    }

    let socket = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;
    const RECONNECT_TIMEOUT = 3000; // 3 seconds

    function createWebSocket() {
        const host = constructWebSocketUrl();
        
        try {
            console.log('Attempting WebSocket connection:', {
                url: host,
                timestamp: new Date().toISOString()
            });

            socket = new WebSocket(host);

            socket.onopen = function(event) {
                console.log('WebSocket connection established', {
                    host,
                    timestamp: new Date().toISOString()
                });
                
                reconnectAttempts = 0;

                // Send initial connection message
                socket.send(JSON.stringify({
                    type: 'connect',
                    message: 'Client connected',
                    timestamp: new Date().toISOString()
                }));

                // Optional: Authenticate if user is logged in
                const userId = getUserId();
                if (userId) {
                    authenticateUser(userId);
                }
            };

            socket.onerror = function(error) {
                console.error('WebSocket connection error', {
                    error,
                    host,
                    readyState: socket.readyState,
                    timestamp: new Date().toISOString(),
                    browserInfo: {
                        userAgent: navigator.userAgent,
                        platform: navigator.platform
                    }
                });
            };

            socket.onclose = function(event) {
                console.log('WebSocket connection closed', {
                    wasClean: event.wasClean,
                    code: event.code,
                    reason: event.reason || 'No reason',
                    host,
                    timestamp: new Date().toISOString()
                });
                
                if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
                    reconnectAttempts++;
                    console.log(`Attempting to reconnect (${reconnectAttempts}/${MAX_RECONNECT_ATTEMPTS})...`);
                    
                    setTimeout(createWebSocket, RECONNECT_TIMEOUT * reconnectAttempts);
                } else {
                    console.error('Max reconnection attempts reached. Please refresh the page or check the server.');
                }
            };

            socket.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    console.log('Received WebSocket message:', data);

                    // Handle different message types
                    switch(data.type) {
                        case 'connection_ack':
                            console.log('Connection acknowledged by server');
                            break;
                        case 'notification':
                            displayNotification(data.data);
                            break;
                        case 'personal_notification':
                            displayPersonalNotification(data.data);
                            break;
                        case 'message':
                            displayMessage(data.content);
                            break;
                        default:
                            console.warn('Unknown message type:', data.type);
                    }
                } catch (error) {
                    console.error('Error parsing WebSocket message:', error);
                }
            };
        } catch (error) {
            console.error('Error creating WebSocket', {
                error,
                host,
                stack: error.stack,
                timestamp: new Date().toISOString()
            });
        }
    }

    function authenticateUser(userId) {
        if (socket && socket.readyState === WebSocket.OPEN) {
            socket.send(JSON.stringify({
                type: 'authenticate',
                userId: userId,
                timestamp: new Date().toISOString()
            }));
            console.log('Sent authentication request for user:', userId);
        }
    }

    function getUserId() {
        // Implement logic to retrieve current user ID
        return document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    }

    function displayNotification(notification) {
        const notificationContainer = document.getElementById('notifications');
        if (notificationContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.classList.add('notification', 'global-notification');
            notificationElement.textContent = JSON.stringify(notification);
            notificationContainer.appendChild(notificationElement);
        }
        console.log('Global Notification:', notification);
    }

    function displayPersonalNotification(notification) {
        const notificationContainer = document.getElementById('personal-notifications');
        if (notificationContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.classList.add('notification', 'personal-notification');
            notificationElement.textContent = JSON.stringify(notification);
            notificationContainer.appendChild(notificationElement);
        }
        console.log('Personal Notification:', notification);
    }

    function displayMessage(message) {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.textContent = message;
            messagesContainer.appendChild(messageElement);
        }
        console.log('Message:', message);
    }

    // Initial WebSocket connection
    createWebSocket();
});