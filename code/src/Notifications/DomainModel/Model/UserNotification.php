<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Model;

use Doctrine\ORM\Mapping as ORM;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use Shared\DomainModel\ValueObject\UserId;

#[ORM\Entity]
#[ORM\Table(name: 'user_notifications')]
class UserNotification
{
    #[ORM\Id]
    #[ORM\Column(type: 'user_notification_id', unique: true)]
    private UserNotificationId $id;

    #[ORM\ManyToOne(targetEntity: Notification::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'notification_id', referencedColumnName: 'id')]
    private Notification $notification;

    #[ORM\Column(name: 'notification_id', type: 'notification_id')]
    private NotificationId $notificationId;

    #[ORM\Column(name: 'user_id', type: 'user_id', length: 16)]
    private UserId $userId;

    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $deletedAt;

    #[ORM\Column(name: 'read_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        UserNotificationId $id,
        Notification $notification,
        UserId $userId,
        ?\DateTimeImmutable $deletedAt = null,
        ?\DateTimeImmutable $readAt = null,
    ) {
        $this->id = $id;
        $this->notification = $notification;
        $this->notificationId = $notification->getId();
        $this->userId = $userId;
        $this->deletedAt = $deletedAt;
        $this->readAt = $readAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function setIsRead(): void
    {
        $this->readAt = new \DateTimeImmutable();
    }

    public function setIsDelete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
    }

    public function getId(): UserNotificationId
    {
        return $this->id;
    }

    public function getNotificationId(): NotificationId
    {
        return $this->notificationId;
    }

    public function notification(): Notification
    {
        return $this->notification;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function isRead(): bool
    {
        return null !== $this->readAt;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
