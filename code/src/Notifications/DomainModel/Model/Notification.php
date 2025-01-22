<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Model;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Enum\NotificationType;

readonly class Notification
{
    public function __construct(
        private NotificationId $id,
        private NotificationType $type,
        private TranslatableText $title,
        private TranslatableText $message,
        private ?string $icon,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }

    public function getId(): NotificationId
    {
        return $this->id;
    }

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function getTitle(): TranslatableText
    {
        return $this->title;
    }

    public function getMessage(): TranslatableText
    {
        return $this->message;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
