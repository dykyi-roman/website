<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Exception;

use RuntimeException;

final class AuthenticationException extends RuntimeException
{
    public static function userNotFound(): self
    {
        return new self('User not found. Authentication required.', 401);
    }
}
