<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Event;

final readonly class UserRegistered
{
    public function __construct(
        private string $userId,
        private string $email,
        private string $userType,
        private \DateTimeImmutable $occurredOn
    ) {
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function userType(): string
    {
        return $this->userType;
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
