<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

final readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private UserNotificationRepositoryInterface $userNotificationRepository,
        private RealTimeNotificationDispatcher $notificationDispatcher,
        private NotificationRepositoryInterface $notificationRepository,
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
        $userNotifications = $this->userNotificationRepository->getUserNotifications($userId, $page, $perPage);

        $data = [];
        foreach ($userNotifications->items as $userNotification) {
            $notification = $this->notificationRepository->findById($userNotification->getNotificationId());
            if (null === $notification) {
                continue;
            }
            $data[] = [
                ...$notification->jsonSerialize(),
                ...[
                    'id' => $userNotification->getId()->toRfc4122(),
                    'readAt' => $userNotification->getReadAt(),
                    'createdAt' => $userNotification->getCreatedAt(),
                    'deletedAt' => $userNotification->getDeletedAt(),
                ],
            ];
        }

        return new PaginationDto($data, $userNotifications->page, $userNotifications->limit);
    }
}
