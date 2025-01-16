<?php

declare(strict_types=1);

namespace Notification\DomainModel\Repository;

use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Model\Notification;

interface NotificationRepositoryInterface
{
    public function findById(NotificationId $id): ?Notification;

    public function save(Notification $notification): void;

    public function getMassNotifications(\DateTimeImmutable $since): array;

    public function getActiveNotifications(): array;
}
