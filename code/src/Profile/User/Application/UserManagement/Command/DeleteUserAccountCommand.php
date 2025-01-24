<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Shared\DomainModel\ValueObject\UserId;

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
