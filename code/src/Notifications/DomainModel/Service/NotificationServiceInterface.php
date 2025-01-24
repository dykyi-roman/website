<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;

interface NotificationServiceInterface
{
    public function createUserNotification(Notification $notification, UserId $userId): void;

    public function createNotification(Notification $notification): void;

    public function markAsRead(UserId $userId, UserNotificationId $userNotificationId): void;

    public function markAllAsRead(UserId $userId): void;

    public function markAsDeleted(UserId $userId, UserNotificationId $userNotificationId): void;

    public function markAllAsDeleted(UserId $userId): void;

    public function getUnreadCount(UserId $userId): int;

    /**
     * @return PaginationDto<array<string, mixed>>
     */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto;
}
