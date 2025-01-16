<?php

declare(strict_types=1);

namespace Notification\DomainModel\Service;

use Notification\DomainModel\Model\Notification;

final class NotificationService
{
    public function createNotification(string $type, string $title, string $message, ?string $link = null): Notification
    {
    }

    public function createMassNotification(string $type, string $title, string $message): Notification
    {

    }

    public function markAsRead(int $userId, int $notificationId): void
    {

    }

    public function markAsDeleted(int $userId, int $notificationId): void
    {

    }

    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 20): array
    {

    }
}