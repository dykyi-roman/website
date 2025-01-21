<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\UserNotification;

final class RealTimeNotificationDispatcher
{
    /** @var array<array-key, array<string, mixed>> */
    private array $notifications = [];

    public function __construct(
        private readonly WebSocketServer $webSocketServer,
        private readonly NotificationFormatter $notificationFormatter,
    ) {
    }

    public function dispatch(UserNotification $userNotification): void
    {
        $message = $this->notificationFormatter->transform($userNotification);
        // send $message usage webSocketServer
    }
}
