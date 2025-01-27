<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Event;

use Shared\DomainModel\ValueObject\UserId;

final readonly class UserWentOnlineEvent
{
    public function __construct(
        public UserId $userId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }
}
