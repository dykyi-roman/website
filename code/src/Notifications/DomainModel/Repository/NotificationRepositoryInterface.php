<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Repository;

use Notifications\DomainModel\Enum\NotificationId;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;

interface NotificationRepositoryInterface
{
    /**
     * @throws NotificationNotFoundException
     */
    public function findById(NotificationId $id): Notification;

    /** @return array<Notification> */
    public function getMassNotifications(\DateTimeImmutable $since): array;

    /** @return array<Notification> */
    public function getActiveNotifications(): array;
}
