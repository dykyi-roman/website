<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Event;

final readonly class UserLoggedInEvent
{
    public function __construct(
        public string $id,
        public string $email,
        public \DateTimeImmutable $loggedInAt,
    ) {
    }
}
