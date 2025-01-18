<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Service\NotificationServiceInterface;
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
            $command->notificationId,
            $command->userId,
        );
    }
}
