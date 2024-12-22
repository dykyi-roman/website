<?php

declare(strict_types=1);

namespace Site\Registration\Application\ResetPassword\ValueObject;

final readonly class ResetPasswordResponse
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(
        public bool $success,
        public string $message,
        public array $errors = [],
    ) {
    }
}
