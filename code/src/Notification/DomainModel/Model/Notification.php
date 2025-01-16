<?php

declare(strict_types=1);

namespace Notification\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\NotificationType;

#[ORM\Entity]
#[ORM\Table(name: 'notifications')]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: 'notification_id', unique: true)]
    private NotificationId $id;

    #[ORM\Column(type: 'notification_type')]
    private NotificationType $type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $message;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $link;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $icon;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'expire_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expireAt;

    #[ORM\Column(name: 'is_mass_notification', type: 'boolean')]
    private bool $isMassNotification;

    public function __construct(
        NotificationId $id,
        NotificationType $type,
        string $title,
        string $message,
        ?string $link,
        ?string $icon,
        ?\DateTimeImmutable $expireAt,
        bool $isMassNotification = false,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->link = $link;
        $this->icon = $icon;
        $this->expireAt = $expireAt;
        $this->isMassNotification = $isMassNotification;
        $this->createdAt = new \DateTimeImmutable();
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
