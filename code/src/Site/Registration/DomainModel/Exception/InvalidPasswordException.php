<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Exception;

class InvalidPasswordException extends \DomainException
{
    public function __construct(string $message = 'Invalid password', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
