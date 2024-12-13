<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Shared\Domain\ValueObject\Email;

/**
 * @see LoginUserCommandHandler
 */
final readonly class LoginUserCommand
{
    public function __construct(
        public Email $email,
        public string $password,
    ) {
    }
}
