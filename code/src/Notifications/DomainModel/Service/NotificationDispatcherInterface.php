<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Profile\User\DomainModel\Enum\UserId;

interface NotificationDispatcherInterface
{
    /**
     * @throws \Notifications\DomainModel\Exception\SendSocketMessageException
     */
    public function dispatch(UserId $userId, array $message): void;
}
