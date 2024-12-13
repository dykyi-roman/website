<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Event;

use Symfony\Component\Uid\Uuid;

final readonly class UserLoggedInEvent
{
    public function __construct(
        public Uuid $id,
        public string $email,
        public \DateTimeImmutable $loggedInAt,
    ) {
    }
}
