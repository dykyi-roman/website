<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Exception;

use Profile\User\DomainModel\Enum\UserId;

final class UserNotFoundException extends \RuntimeException
{
    public function __construct(UserId $userId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('User not found by id: %s', $userId->toRfc4122()), $code, $previous);
    }
}
