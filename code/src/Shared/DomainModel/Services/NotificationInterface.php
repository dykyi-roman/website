<?php

declare(strict_types=1);

namespace Shared\DomainModel\Services;

use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\RecipientInterface;

interface NotificationInterface
{
    /**
     * Sends the given notification to the given recipient.
     */
    public function send(Notification $notification, RecipientInterface ...$recipients): void;
}
