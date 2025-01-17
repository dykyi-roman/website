<?php

declare(strict_types=1);

namespace Notification\DomainModel\Model;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\NotificationType;

class Notification
{
    public function __construct(
        private NotificationId $id,
        private NotificationType $type,
        private string $title,
        private string $message,
        private ?string $link,
        private ?string $icon,
        private bool $isMassNotification = false,
        private ?\DateTimeImmutable $expireAt = null,
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpireAt(): ?\DateTimeImmutable
    {
        return $this->expireAt;
    }

    public function isMassNotification(): bool
    {
        return $this->isMassNotification;
    }
}
