<?php

declare(strict_types=1);

namespace Notifications\Application\CreateNotification\Command;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;

/**
 * @see CreateNotificationCommandHandler
 */
final readonly class CreateNotificationCommand
{
    public function __construct(
        public NotificationId $id,
        public NotificationName $name,
        public NotificationType $type,
        public TranslatableText $title,
        public TranslatableText $message,
        public ?string $icon = null,
        public ?string $link = null,
    ) {
    }
}
