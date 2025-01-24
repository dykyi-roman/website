<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Shared\DomainModel\ValueObject\UserId;

/**
 * @see CreateNotificationMessageCommandHandler
 */
final class CreateNotificationMessageCommand
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
