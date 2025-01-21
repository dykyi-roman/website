document.addEventListener('DOMContentLoaded', () => {
    // Dynamically determine WebSocket connection parameters
    const socketConfig = {
        protocol: window.location.protocol === 'https:' ? 'wss' : 'ws',
        host: window.location.host,
        path: '/ws'
    };

    // Construct WebSocket URL dynamically
    function constructWebSocketUrl() {
        const url = `${socketConfig.protocol}://${socketConfig.host}${socketConfig.path}`;
        console.log('Constructed WebSocket URL:', {
            url,
            protocol: socketConfig.protocol,
            host: socketConfig.host,
            path: socketConfig.path,
            fullLocation: window.location
        });
        return url;
    }

    let socket = null;
    let reconnectAttempts = 0;
    const MAX_RECONNECT_ATTEMPTS = 5;
    const RECONNECT_TIMEOUT = 3000; // 3 seconds

    function createWebSocket() {
        const url = constructWebSocketUrl();
        
        try {
            console.log('Attempting WebSocket connection:', {
                url,
                timestamp: new Date().toISOString()
            });

            socket = new WebSocket(url);

            socket.onopen = function(event) {
                console.log('WebSocket connection established', {
                    url,
                    timestamp: new Date().toISOString()
                });
                
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
                console.log('WebSocket connection closed', {
                    code: event.code,
                    reason: event.reason,
                    wasClean: event.wasClean,
                    timestamp: new Date().toISOString()
                });

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
            notificationElement.textContent = notification.message;
            notificationContainer.appendChild(notificationElement);

            // Auto-remove notification after 5 seconds
            setTimeout(() => {
                notificationElement.remove();
            }, 5000);
        }
    }

    // Initial WebSocket connection
    createWebSocket();
});