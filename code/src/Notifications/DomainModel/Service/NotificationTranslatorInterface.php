<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\Notification;

interface NotificationTranslatorInterface
{
    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     message: string,
     *     icon: string|null
     * }
     */
    public function translateNotification(Notification $notification): array;
}
