<?php

declare(strict_types=1);

namespace Notification\DomainModel\Repository;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Model\Notification;

interface NotificationRepositoryInterface
{
    public function findById(NotificationId $id): ?Notification;

    /** @return array<Notification> */
    public function getMassNotifications(\DateTimeImmutable $since): array;

    /** @return array<Notification> */
    public function getActiveNotifications(): array;
}
