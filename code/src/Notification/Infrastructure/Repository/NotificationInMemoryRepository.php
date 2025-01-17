<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Repository;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\NotificationType;
use Notification\DomainModel\Model\Notification;
use Notification\DomainModel\Repository\NotificationRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NotificationInMemoryRepository implements NotificationRepositoryInterface
{
    /** @var array<string, Notification> */
    private array $notifications;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->notifications = [
            NotificationId::HAPPY_BIRTHDAY->value => new Notification(
                NotificationId::HAPPY_BIRTHDAY,
                NotificationType::PERSONAL,
                $translator->trans('notification.happy-birthday.title'),
                $translator->trans('notification.happy-birthday.message'),
                null,
                null,
            ),
            NotificationId::HAPPY_NEW_YEAR->value => new Notification(
                NotificationId::HAPPY_NEW_YEAR,
                NotificationType::PERSONAL,
                $translator->trans('notification.happy-new_year.title'),
                $translator->trans('notification.happy-new_year.message'),
                null,
                null,
            ),
            NotificationId::PASS_VERIFICATION->value => new Notification(
                NotificationId::PASS_VERIFICATION,
                NotificationType::INFORMATION,
                $translator->trans('notification.pass-verification.title'),
                $translator->trans('notification.pass-verification.message'),
                null,
                null,
            ),
        ];
    }

    public function findById(NotificationId $id): ?Notification
    {
        $key = $id->value;
        return isset($this->notifications[$key]) ? $this->notifications[$key] : null;
    }

    /** @return array<Notification> */
    public function getMassNotifications(\DateTimeImmutable $since): array
    {
        return array_filter($this->notifications, fn(Notification $notification) => 
            $notification->getType() === NotificationType::SYSTEM
        );
    }

    /** @return array<Notification> */
    public function getActiveNotifications(): array
    {
        return array_values($this->notifications);
    }
}
