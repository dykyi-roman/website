<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $phone,
        public string $country,
        public string $city,
        public bool $isPartner
    ) {
    }
}
