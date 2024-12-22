<?php

declare(strict_types=1);

namespace Site\Registration\Application\ForgontPassword\Command;

/**
 * @see ForgotPasswordCommandHandler
 */
final readonly class ForgotPasswordCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
