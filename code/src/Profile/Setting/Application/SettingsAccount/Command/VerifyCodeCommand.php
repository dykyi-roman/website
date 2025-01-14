<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsAccount\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see VerifyCodeHandler
 */
final readonly class VerifyCodeCommand
{
    public function __construct(
        public UserId $userId,
        public string $type,
        public string $code,
    ) {
    }
}
