<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Repository;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\TranslatableText;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;

final class NotificationInMemoryRepository implements NotificationRepositoryInterface
{
    /** @var array<string, Notification> */
    private array $notifications;

    public function __construct()
    {
        $this->notifications = [
            NotificationId::HAPPY_BIRTHDAY->value => new Notification(
                NotificationId::HAPPY_BIRTHDAY,
                NotificationType::PERSONAL,
                new TranslatableText('notifications.notification.happy-birthday.title'),
                new TranslatableText('notifications.notification.happy-birthday.message'),
                null,
                null,
            ),
            NotificationId::HAPPY_NEW_YEAR->value => new Notification(
                NotificationId::HAPPY_NEW_YEAR,
                NotificationType::PERSONAL,
                new TranslatableText('notifications.notification.happy-new_year.title'),
                new TranslatableText('notifications.notification.happy-new_year.message'),
                null,
                null,
            ),
            NotificationId::PASS_VERIFICATION->value => new Notification(
                NotificationId::PASS_VERIFICATION,
                NotificationType::INFORMATION,
                new TranslatableText('notifications.notification.pass-verification.title'),
                new TranslatableText('notifications.notification.pass-verification.message'),
                null,
                null,
            ),
        ];
    }

    public function findById(NotificationId $id): Notification
    {
        return $this->notifications[$id->value] ?? throw new NotificationNotFoundException($id);
    }

    /** @return array<Notification> */
    public function getMassNotifications(\DateTimeImmutable $since): array
    {
        return array_filter(
            $this->notifications,
            fn(Notification $notification) => NotificationType::SYSTEM === $notification->getType()
        );
    }

    /** @return array<Notification> */
    public function getActiveNotifications(): array
    {
        return array_values($this->notifications);
    }
}
