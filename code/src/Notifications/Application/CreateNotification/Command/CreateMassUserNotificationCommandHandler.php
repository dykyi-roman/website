<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateMassUserNotificationCommandHandler
{
    public function __construct(
        private NotificationServiceInterface $notificationService,
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function __invoke(CreateMassUserNotificationCommand $command): void
    {
        $notification = $this->notificationRepository->findById($command->notificationId);
        foreach ($command->userIds as $userId) {
            $this->notificationService->createUserNotification(
                $notification,
                $userId,
            );
        }
    }
}
