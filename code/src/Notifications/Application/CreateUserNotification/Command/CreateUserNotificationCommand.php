<?php

declare(strict_types=1);

namespace Notifications\Application\CreateUserNotification\Command;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Shared\DomainModel\ValueObject\UserId;

/**
 * @see CreateUserNotificationCommandHandler
 */
final readonly class CreateUserNotificationCommand
{
    public function __construct(
        public UserId $userId,
        public NotificationName $name,
        public NotificationType $type,
        public TranslatableText $title,
        public TranslatableText $message,
        public ?string $icon = null,
        public ?string $link = null,
    ) {
    }
}
