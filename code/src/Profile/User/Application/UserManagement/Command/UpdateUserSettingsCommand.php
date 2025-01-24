<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Shared\DomainModel\ValueObject\UserId;

/**
 * @see UpdateUserSettingsCommandHandler
 */
final readonly class UpdateUserSettingsCommand
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
