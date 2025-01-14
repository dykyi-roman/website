<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsAccount\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see SendVerificationCodeHandler
 */
final readonly class SendVerificationCodeCommand
{
    public function __construct(
        public UserId $userId,
        public string $type,
        public string $recipient,
    ) {
    }
}
