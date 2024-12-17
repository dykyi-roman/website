<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

/**
 * @see \App\Registration\Application\Command\PasswordResetCommandHandler
 */
final readonly class PasswordResetCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
