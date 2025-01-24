<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Shared\DomainModel\ValueObject\UserId;

interface NotificationDispatcherInterface
{
    /**
     * @param array<string, mixed> $message
     *
     * @throws \Notifications\DomainModel\Exception\SendSocketMessageException
     */
    public function dispatch(UserId $userId, array $message): void;
}
