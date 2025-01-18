<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see DeleteUserAccountCommandHandler
 */
final readonly class DeleteUserAccountCommand
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}
