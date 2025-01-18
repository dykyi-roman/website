<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Enum\NotificationId;
use Profile\User\DomainModel\Enum\UserId;

/**
 * @see CreateNotificationMessageCommandHandler
 */
final class CreateNotificationMessageCommand
{
    public function __construct(
        public NotificationId $notificationId,
        public UserId $userId,
    ) {
    }
}
