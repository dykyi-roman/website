<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\DomainModel\ValueObject\NotificationId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateNotificationMessageCommandHandler
{
    public function __construct(
        private NotificationServiceInterface $notificationService,
    ) {
    }

    public function __invoke(CreateNotificationMessageCommand $command): void
    {
        $this->notificationService->createNotification(
            new Notification(
                new NotificationId(),
                $command->name,
                $command->type,
                $command->title,
                $command->message,
            ),
            $command->userId,
        );
    }
}
