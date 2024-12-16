<?php

declare(strict_types=1);

namespace App\Shared\DomainModel\Services;

use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\RecipientInterface;

interface NotificationInterface
{
    /**
     * Sends the given notification to the given recipient.
     */
    public function send(Notification $notification, RecipientInterface ...$recipients): void;
}
