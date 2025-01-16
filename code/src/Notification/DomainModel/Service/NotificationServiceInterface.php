<?php

namespace Notification\DomainModel\Service;

use Notification\DomainModel\Enum\NotificationType;
use Notification\DomainModel\Model\Notification;
use Profile\User\DomainModel\Enum\UserId;

interface NotificationServiceInterface
{
    public function createNotification(
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): Notification;

    public function createMassNotification(NotificationType $type, string $title, string $message): Notification;

    public function markAsRead(UserId $userId, int $notificationId): void;

    public function markAsDeleted(UserId $userId, int $notificationId): void;

    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): array;
}