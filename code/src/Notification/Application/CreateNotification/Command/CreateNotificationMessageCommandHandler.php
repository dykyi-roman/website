<?php

declare(strict_types=1);

namespace Notification\Application\CreateNotification\Command;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateNotificationMessageCommandHandler
{
    public function __invoke(CreateNotificationMessageCommand $command)
    {
    }
}