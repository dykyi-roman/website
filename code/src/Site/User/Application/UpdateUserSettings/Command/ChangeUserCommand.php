<?php

declare(strict_types=1);

namespace Site\User\Application\UpdateUserSettings\Command;

use Site\User\DomainModel\Enum\UserId;

/**
 * @see ChangeUserCommandHandler
 */
final readonly class ChangeUserCommand
{
    public function __construct(
        public UserId $userId,
        public string $name,
        public string $email,
        public string $phone,
        public ?string $avatar = null,
    ) {
    }
}
