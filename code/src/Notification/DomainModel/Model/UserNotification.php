<?php

declare(strict_types=1);

namespace Notification\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\UserNotificationId;
use Profile\User\DomainModel\Enum\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'user_notifications')]
class UserNotification
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_notification_id', unique: true)]
    private UserNotificationId $id;

    #[ORM\Column(name: 'notification_id', type: 'notification_id')]
    private NotificationId $notificationId;

    #[ORM\Column(name: 'user_id', type: 'user_id', length: 16)]
    private UserId $userId;

    #[ORM\Column(name: 'is_read', type: 'boolean')]
    private bool $isRead;

    #[ORM\Column(name: 'is_deleted', type: 'boolean')]
    private bool $isDeleted;

    #[ORM\Column(name: 'read_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        UserNotificationId $id,
        NotificationId $notificationId,
        UserId $userId,
        bool $isRead = false,
        bool $isDeleted = false,
        ?\DateTimeImmutable $readAt = null,
    ) {
        $this->id = $id;
        $this->notificationId = $notificationId;
        $this->userId = $userId;
        $this->isRead = $isRead;
        $this->isDeleted = $isDeleted;
        $this->readAt = $readAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UserNotificationId
    {
        return $this->id;
    }

    public function getNotificationId(): NotificationId
    {
        return $this->notificationId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
