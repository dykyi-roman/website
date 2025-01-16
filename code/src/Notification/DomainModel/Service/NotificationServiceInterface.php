<?php

namespace Notification\DomainModel\Service;

use Symfony\Component\Notifier\Notification\Notification;

interface NotificationServiceInterface
{
    public function createNotification(
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): Notification;

    public function createMassNotification(string $type, string $title, string $message): Notification;

    public function markAsRead(int $userId, int $notificationId): void;

    public function markAsDeleted(int $userId, int $notificationId): void;

    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 20): array;
}