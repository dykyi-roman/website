<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Exception;

class PasswordIsNotMatchException extends \DomainException
{
    public function __construct(string $message = 'Passwords do not match', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
