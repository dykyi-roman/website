<?php

namespace Notification\DomainModel\Service;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\UserNotificationId;
use Notification\DomainModel\Model\UserNotification;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

interface NotificationServiceInterface
{
    public function createNotification(NotificationId $notificationId, UserId $userId): void;

    public function markAsRead(UserId $userId, UserNotificationId $userNotificationId): void;

    public function markAsDeleted(UserId $userId, UserNotificationId $userNotificationId): void;

    public function getUnreadCount(UserId $userId): int;

    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto;
}
