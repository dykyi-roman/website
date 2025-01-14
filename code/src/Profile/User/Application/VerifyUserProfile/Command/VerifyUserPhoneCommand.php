<?php

declare(strict_types=1);

namespace Profile\User\Application\VerifyUserProfile\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see VerifyUserPhoneCommandHandler
 */
final class VerifyUserPhoneCommand
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}
