<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\UserNotification;

final readonly class RealTimeNotificationDispatcher
{
    public function __construct(
        private WebSocketServer $webSocketServer,
        private NotificationFormatter $notificationFormatter,
    ) {
    }

    public function dispatch(UserNotification $userNotification): void
    {
        $message = $this->notificationFormatter->transform($userNotification);
        
        // Broadcast the message to all connected WebSocket clients
        foreach (WebSocketServer::$connections as $connection) {
            $connection->send(json_encode([
                'type' => 'notification',
                'data' => $message
            ]));
        }
    }

    /**
     * Send a message to a specific user
     */
    public function sendToUser(string $userId, UserNotification $userNotification): void
    {
        $message = $this->notificationFormatter->transform($userNotification);
        
        // Find and send to the specific user's connection
        if (isset(WebSocketServer::$authorizedConnections[$userId])) {
            $connection = WebSocketServer::$authorizedConnections[$userId];
            $connection->send(json_encode([
                'type' => 'personal_notification',
                'userId' => $userId,
                'data' => $message
            ]));
        }
    }
}
