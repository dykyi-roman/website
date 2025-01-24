<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Exception;

use Shared\DomainModel\ValueObject\UserId;

final class UserExistException extends \RuntimeException
{
    public function __construct(UserId $userId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('User exist with email for id: %s', $userId->toRfc4122()), $code, $previous);
    }
}
