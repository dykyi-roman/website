<?php

declare(strict_types=1);

namespace Shared\DomainModel\Exception;

final class AuthenticationException extends \RuntimeException
{
    public static function userNotFound(): self
    {
        return new self('User not found. Authentication required.', 401);
    }
}
