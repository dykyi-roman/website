<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

final readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private UserNotificationRepositoryInterface $userNotificationRepository,
        private RealTimeNotificationDispatcher $notificationDispatcher,
        private NotificationCache $cache,
    ) {
    }

    public function createNotification(NotificationId $notificationId, UserId $userId): void
    {
        $userNotification = new UserNotification(new UserNotificationId(), $notificationId, $userId);
        $this->userNotificationRepository->save($userNotification);

        $this->cache->incrementUnreadCount($userId);

        $this->notificationDispatcher->dispatch($userNotification);
    }

    public function markAsRead(UserId $userId, UserNotificationId $userNotificationId): void
    {
        $userNotification = $this->userNotificationRepository->findById($userNotificationId);
        if (!$userNotification->isRead()) {
            $userNotification->setIsRead();
            $this->userNotificationRepository->save($userNotification);
            $this->cache->decrementUnreadCount($userId);
        }
    }

    public function markAsDeleted(UserId $userId, UserNotificationId $userNotificationId): void
    {
        $userNotification = $this->userNotificationRepository->findById($userNotificationId);
        if (!$userNotification->isRead()) {
            $userNotification->setIsDelete();
            $this->userNotificationRepository->save($userNotification);
            $this->cache->decrementUnreadCount($userId);
        }
    }

    public function getUnreadCount(UserId $userId): int
    {
        $count = $this->cache->getUnreadCount($userId);
        if (null === $count) {
            $count = $this->userNotificationRepository->getUnreadCount($userId);
        }

        return $count;
    }

    /** @return PaginationDto<UserNotification> */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto
    {
        return $this->userNotificationRepository->getUserNotifications($userId, $page, $perPage);
    }
}
