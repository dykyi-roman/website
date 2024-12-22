<?php

declare(strict_types=1);

namespace Site\Registration\Application\UserRegistration\Command;

use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;

/**
 * @see RegisterUserCommandHandler
 */
final readonly class RegisterUserCommand
{
    public function __construct(
        public string $name,
        public Email $email,
        public string $password,
        public ?string $phone,
        public Location $location,
    ) {
    }
}
