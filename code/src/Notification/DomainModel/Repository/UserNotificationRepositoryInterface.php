<?php

declare(strict_types=1);

namespace Notification\DomainModel\Repository;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\UserNotificationId;
use Notification\DomainModel\Model\UserNotification;
use Profile\User\DomainModel\Enum\UserId;

interface UserNotificationRepositoryInterface
{
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): array;

    public function findById(UserNotificationId $id): UserNotification;

    public function getUnreadCount(UserId $userId): int;

    public function save(UserNotification $userNotification): void;
}
