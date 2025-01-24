<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;

final readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private UserNotificationRepositoryInterface $userNotificationRepository,
        private NotificationDispatcherInterface $notificationDispatcher,
        private NotificationFormatter $notificationFormatter,
        private LoggerInterface $logger,
        private NotificationCache $cache,
    ) {
    }

    public function createUserNotification(Notification $notification, UserId $userId): void
    {
        $userNotification = new UserNotification(new UserNotificationId(), $notification, $userId);
        $this->userNotificationRepository->save($userNotification);

        $this->cache->incrementUnreadCount($userId);

        try {
            $this->notificationDispatcher->dispatch(
                $userNotification->getUserId(),
                $this->notificationFormatter->transform($userNotification),
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function createNotification(Notification $notification): void
    {
        $this->notificationRepository->save($notification);
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

    public function markAllAsRead(UserId $userId): void
    {
        $this->userNotificationRepository->markAllAsRead($userId);
        $this->cache->resetUnreadCount($userId);
    }

    public function markAsDeleted(UserId $userId, UserNotificationId $userNotificationId): void
    {
        $userNotification = $this->userNotificationRepository->findById($userNotificationId);
        if (!$userNotification->isDeleted()) {
            $userNotification->setIsDelete();
            if (!$userNotification->isRead()) {
                $this->cache->decrementUnreadCount($userId);
            }
            $this->userNotificationRepository->save($userNotification);
        }
    }

    public function markAllAsDeleted(UserId $userId): void
    {
        $this->userNotificationRepository->markAllAsDeleted($userId);
        $this->cache->resetUnreadCount($userId);
    }

    public function getUnreadCount(UserId $userId): int
    {
        $count = $this->cache->getUnreadCount($userId);
        if (null === $count) {
            $count = $this->userNotificationRepository->getUnreadCount($userId);
        }

        return $count;
    }

    /**
     * @return PaginationDto<array<string, mixed>>
     */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto
    {
        $userNotifications = $this->userNotificationRepository->getUserNotifications($userId, $page, $perPage);

        /** @var array<array<string, mixed>> $data */
        $data = [];
        foreach ($userNotifications->items as $userNotification) {
            try {
                $transformed = $this->notificationFormatter->transform($userNotification);
            } catch (NotificationNotFoundException $exception) {
                $this->logger->error($exception->getMessage());

                continue;
            }

            $data[] = $transformed;
        }

        return new PaginationDto($data, $userNotifications->page, $userNotifications->limit);
    }
}
