<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateNotificationCommandHandler
{
    public function __construct(
        private NotificationServiceInterface $notificationService,
    ) {
    }

    public function __invoke(CreateNotificationCommand $command): void
    {
        $this->notificationService->createNotification(
            new Notification(
                $command->id,
                $command->name,
                $command->type,
                $command->title,
                $command->message,
            ),
        );
    }
}
