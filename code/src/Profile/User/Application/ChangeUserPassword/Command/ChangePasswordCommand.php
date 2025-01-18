<?php

declare(strict_types=1);

namespace Profile\User\Application\ChangeUserPassword\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see ChangePasswordCommandHandler
 */
final readonly class ChangePasswordCommand
{
    public function __construct(
        public UserId $userId,
        public string $currentPassword,
        public string $newPassword,
    ) {
    }
}
