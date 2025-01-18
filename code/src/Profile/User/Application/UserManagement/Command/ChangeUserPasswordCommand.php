<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see ChangeUserPasswordCommandHandler
 */
final readonly class ChangeUserPasswordCommand
{
    public function __construct(
        public UserId $userId,
        public string $currentPassword,
        public string $newPassword,
    ) {
    }
}
