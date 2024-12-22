<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Event;

use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;

final readonly class UserRegisteredEvent
{
    public function __construct(
        public UserId $id,
        public Email $email,
        public \DateTimeImmutable $registeredAt,
    ) {
    }
}
