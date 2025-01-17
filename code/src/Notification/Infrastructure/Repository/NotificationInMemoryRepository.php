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
    /**
     * @var Notification[]
     */
    private array $notifications;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->notifications[] = [
            new Notification(
                NotificationId::HAPPY_BIRTHDAY,
                NotificationType::PERSONAL,
                $translator->trans('notification.happy-birthday.title'),
                $translator->trans('notification.happy-birthday.message'),
                null,
                null,
            ),
            new Notification(
                NotificationId::HAPPY_NEW_YEAR,
                NotificationType::PERSONAL,
                $translator->trans('notification.happy-new_year.title'),
                $translator->trans('notification.happy-new_year.message'),
                null,
                null,
            ),
            new Notification(
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
        return null;
    }

    public function getMassNotifications(\DateTimeImmutable $since): array
    {
        return [];
    }

    public function getActiveNotifications(): array
    {
        return [];
    }
}
