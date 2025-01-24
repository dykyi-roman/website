<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Exception;

use Shared\DomainModel\ValueObject\UserId;

final class UserChangeDataException extends \RuntimeException
{
    public function __construct(UserId $userId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('User change data error. User Id: %s', $userId->toRfc4122()), $code, $previous);
    }
}
