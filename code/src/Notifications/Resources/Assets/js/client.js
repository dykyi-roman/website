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

                // Send initial connection message with protocol version
                socket.send(JSON.stringify({
                    type: 'connect',
                    message: 'Client connected',
                    version: '13',  // WebSocket protocol version
                    timestamp: new Date().toISOString()
                }));

                // Check connection status after a short delay
                setTimeout(() => {
                    socket.send(JSON.stringify({
                        type: 'status',
                        timestamp: new Date().toISOString()
                    }));
                }, 1000);

                // Set up periodic ping
                setInterval(() => {
                    if (socket.readyState === WebSocket.OPEN) {
                        socket.send(JSON.stringify({
                            type: 'ping',
                            timestamp: new Date().toISOString()
                        }));
                    }
                }, 30000);

                // Optional: Authenticate if user is logged in
                const userId = getUserId();
                if (userId) {
                    authenticateUser(userId);
                }
            };

            socket.onerror = function(error) {
                console.error('WebSocket connection error', {
                    error,
                    url,
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
                    url,
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
                    const message = JSON.parse(event.data);
                    console.log('Received message:', message);

                    switch (message.type) {
                        case 'connection_ack':
                            console.log('Connection acknowledged by server', message);
                            break;

                        case 'status':
                            console.log('Connection status:', message.status);
                            break;

                        case 'pong':
                            console.log('Server responded to ping', message);
                            break;

                        case 'notification':
                            displayNotification(message.data);
                            break;

                        case 'personal_notification':
                            displayPersonalNotification(message.data);
                            break;

                        case 'message':
                            displayMessage(message.content);
                            break;

                        case 'error':
                            console.error('Server error:', message);
                            break;

                        default:
                            console.log('Unhandled message type:', message);
                    }
                } catch (error) {
                    console.error('Error processing message:', error, event.data);
                }
            };
        } catch (error) {
            console.error('Error creating WebSocket', {
                error,
                url,
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