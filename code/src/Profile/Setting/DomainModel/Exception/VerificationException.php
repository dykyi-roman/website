<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Exception;

class VerificationException extends \RuntimeException
{
    public static function invalidCode(): self
    {
        return new self('Invalid verification code');
    }

    public static function codeExpired(): self
    {
        return new self('Verification code has expired');
    }

    public static function tooManyAttempts(): self
    {
        return new self('Too many verification attempts');
    }

    public static function tooManyRequests(): self
    {
        return new self('Too many code generation requests');
    }
}
