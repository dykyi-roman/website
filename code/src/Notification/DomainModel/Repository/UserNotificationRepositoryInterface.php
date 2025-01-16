<?php

declare(strict_types=1);

namespace Notification\DomainModel\Repository;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Model\UserNotification;
use Profile\User\DomainModel\Enum\UserId;

interface UserNotificationRepositoryInterface
{
    public function findByUserId(UserId $userId, int $page = 1, int $perPage = 20): array;

    public function getUnreadCount(UserId $userId): int;

    public function markAsRead(UserId $userId, NotificationId $notificationId): void;

    public function markAsDeleted(UserId $userId, NotificationId $notificationId): void;

    public function save(UserNotification $userNotification): void;
}