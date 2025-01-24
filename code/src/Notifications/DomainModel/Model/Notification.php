<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;

#[ORM\Entity]
#[ORM\Table(name: 'notifications')]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'notification_id')]
    private NotificationId $id;

    #[ORM\Column(name: 'name', type: 'notification_name')]
    private NotificationName $name;

    #[ORM\Column(name: 'type', type: 'notification_type')]
    private NotificationType $type;

    #[ORM\Column(name: 'title', type: 'translatable_text')]
    private TranslatableText $title;

    #[ORM\Column(name: 'message', type: 'translatable_text')]
    private TranslatableText $message;

    #[ORM\Column(name: 'icon', type: 'string')]
    private ?string $icon;

    #[ORM\Column(name: 'link', type: 'string')]
    private ?string $link;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        NotificationId $id,
        NotificationName $name,
        NotificationType $type,
        TranslatableText $title,
        TranslatableText $message,
        ?string $icon = null,
        ?string $link = null,
        \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->icon = $icon;
        $this->link = $link;
        $this->createdAt = $createdAt;
    }

    public function getId(): NotificationId
    {
        return $this->id;
    }

    public function getName(): NotificationName
    {
        return $this->name;
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
