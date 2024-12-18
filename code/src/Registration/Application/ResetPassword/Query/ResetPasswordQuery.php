<?php

declare(strict_types=1);

namespace App\Registration\Application\ResetPassword\Query;

/**
 * @see \App\Registration\Application\ResetPassword\Query\ResetPasswordQueryHandler
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
