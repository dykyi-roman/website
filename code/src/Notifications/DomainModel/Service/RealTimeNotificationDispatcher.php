<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\UserNotification;

final class RealTimeNotificationDispatcher
{
    /** @var array<array-key, array<string, mixed>> */
    private array $notifications = [];

    public function __construct(
        private readonly NotificationFormatter $notificationFormatter,
    ) {
    }

    public function dispatch(UserNotification $userNotification): void
    {
        $this->notifications[] = $this->notificationFormatter->transform($userNotification);
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getNotifications(): array
    {
        $notifications = $this->notifications;
        $this->notifications = []; // Clear the queue after retrieving

        return $notifications;
    }
}
