<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Exception;

class TokenExpiredException extends \DomainException
{
    public function __construct(string $message = 'Reset token is invalid or has expired', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
