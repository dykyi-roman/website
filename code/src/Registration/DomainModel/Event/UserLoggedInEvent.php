<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Event;

use App\Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

final readonly class UserLoggedInEvent
{
    public function __construct(
        public Uuid $id,
        public Email $email,
        public \DateTimeImmutable $loggedInAt,
    ) {
    }
}
