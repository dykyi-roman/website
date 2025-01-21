document.addEventListener('DOMContentLoaded', () => {
    const host = 'ws://127.0.0.1:1001';
    const socket = new WebSocket(host);

    socket.onopen = function(event) {
        console.log('WebSocket connection established');
        // Optional: Send initial connection message
        socket.send(JSON.stringify({
            type: 'connect',
            message: 'Client connected'
        }));
    };

    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);
        console.log('Received message:', data);

        // Handle different message types
        switch(data.type) {
            case 'notification':
                displayNotification(data.data);
                break;
            case 'message':
                displayMessage(data.content);
                break;
            default:
                console.warn('Unknown message type:', data.type);
        }
    };

    socket.onerror = function(error) {
        console.error('WebSocket error:', error);
    };

    socket.onclose = function(event) {
        console.log('WebSocket connection closed');
    };

    function displayNotification(notification) {
        const notificationContainer = document.getElementById('notifications');
        if (notificationContainer) {
            const notificationElement = document.createElement('div');
            notificationElement.classList.add('notification');
            notificationElement.textContent = JSON.stringify(notification);
            notificationContainer.appendChild(notificationElement);
        }
    }

    function displayMessage(message) {
        const messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.textContent = message;
            messagesContainer.appendChild(messageElement);
        }
    }
});