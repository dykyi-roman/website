<?php

declare(strict_types=1);

namespace Notification\DomainModel\Service;

use Notification\DomainModel\Enum\NotificationType;
use Notification\DomainModel\Model\Notification;
use Notification\DomainModel\Repository\NotificationRepositoryInterface;
use Notification\DomainModel\Repository\UserNotificationRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Services\MessageBusInterface;

final readonly class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private UserNotificationRepositoryInterface $userNotificationRepository,
        private NotificationDispatcher $dispatcher,
        private MessageBusInterface $messageBus,
        private NotificationCache $cache,
    ) {
    }

    public function createNotification(NotificationType $type, string $title, string $message, ?string $link = null): Notification
    {
        $notification = new Notification(
        );

        $this->notificationRepository->save($notification);
    }

    public function createMassNotification(NotificationType $type, string $title, string $message): Notification
    {
    }

    public function markAsRead(UserId $userId, int $notificationId): void
    {
    }

    public function markAsDeleted(UserId $userId, int $notificationId): void
    {
    }

    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): array
    {
    }
}
