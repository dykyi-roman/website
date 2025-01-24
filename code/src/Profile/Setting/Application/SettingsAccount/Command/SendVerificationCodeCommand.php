<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsAccount\Command;

use Shared\DomainModel\ValueObject\UserId;

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
