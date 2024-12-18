<?php

declare(strict_types=1);

namespace App\Registration\Application\ForgontPassword\Command;

/**
 * @see \App\Registration\Application\ForgontPassword\Command\ForgotPasswordCommandHandler
 */
final readonly class ForgotPasswordCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
