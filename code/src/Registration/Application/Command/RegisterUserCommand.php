<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Shared\Domain\ValueObject\Email;
use App\Shared\Domain\ValueObject\Location;

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
        public bool $isPartner,
    ) {
    }
}
