<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Enum\UserStatus;

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
