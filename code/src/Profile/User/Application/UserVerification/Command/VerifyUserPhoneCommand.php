<?php

declare(strict_types=1);

namespace Profile\User\Application\UserVerification\Command;

use Shared\DomainModel\ValueObject\UserId;

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
