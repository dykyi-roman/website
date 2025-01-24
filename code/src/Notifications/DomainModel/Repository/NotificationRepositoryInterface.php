<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Repository;

use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\ValueObject\NotificationId;

interface NotificationRepositoryInterface
{
    /**
     * @throws NotificationNotFoundException
     */
    public function findById(NotificationId $id): Notification;

    public function save(Notification ...$notifications): void;
}
