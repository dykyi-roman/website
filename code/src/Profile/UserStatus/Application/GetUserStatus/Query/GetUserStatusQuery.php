<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\GetUserStatus\Query;

use Shared\DomainModel\ValueObject\UserId;

/**
 * @see \Profile\UserStatus\Application\GetUserStatus\Query\GetUserStatusQueryHandler
 */
final class GetUserStatusQuery
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}