<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Enum\UserNotificationId;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

interface NotificationServiceInterface
{
    public function createNotification(NotificationId $notificationId, UserId $userId): void;

    public function markAsRead(UserId $userId, UserNotificationId $userNotificationId): void;

    public function markAsDeleted(UserId $userId, UserNotificationId $userNotificationId): void;

    public function getUnreadCount(UserId $userId): int;

    /**
     * @return PaginationDto<array<string, mixed>>
     */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto;
}
