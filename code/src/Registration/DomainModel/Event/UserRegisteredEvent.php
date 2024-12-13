<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Event;

final readonly class UserRegisteredEvent
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $isPartner,
        public \DateTimeImmutable $registeredAt,
    ) {
    }
}
