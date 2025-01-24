<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\ValueObject\NotificationId;
use Shared\DomainModel\ValueObject\UserId;

/**
 * @see CreateMassUserNotificationCommandHandler
 */
final readonly class CreateMassUserNotificationCommand
{
    /**
     * @var UserId[]
     */
    public array $userIds;

    public function __construct(
        public NotificationId $notificationId,
        UserId ...$userIds,
    ) {
        $this->userIds = $userIds;
    }
}
