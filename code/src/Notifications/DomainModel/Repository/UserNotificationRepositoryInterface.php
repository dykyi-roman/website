<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Repository;

use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\UserNotification;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

interface UserNotificationRepositoryInterface
{
    /** @return PaginationDto<UserNotification> */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto;

    /**
     * @throws NotificationNotFoundException
     */
    public function findById(UserNotificationId $id): UserNotification;

    public function getUnreadCount(UserId $userId): int;

    public function save(UserNotification $userNotification): void;

    public function markAllAsDeleted(UserId $userId): void;

    public function markAllAsRead(UserId $userId): void;
}
