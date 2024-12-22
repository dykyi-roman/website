<?php

declare(strict_types=1);

namespace Site\Registration\Application\ResetPassword\Query;

/**
 * @see ResetPasswordQueryHandler
 */
final readonly class ResetPasswordQuery
{
    public function __construct(
        public string $password,
        public string $confirmPassword,
        public string $token,
    ) {
    }
}
