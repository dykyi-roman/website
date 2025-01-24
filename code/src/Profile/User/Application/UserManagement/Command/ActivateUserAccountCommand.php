<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Profile\User\DomainModel\Enum\UserStatus;
use Shared\DomainModel\ValueObject\UserId;

/**
 * @see ActivateUserAccountCommandHandler
 */
final readonly class ActivateUserAccountCommand
{
    public function __construct(
        public UserId $userId,
        public UserStatus $userStatus,
    ) {
    }
}
