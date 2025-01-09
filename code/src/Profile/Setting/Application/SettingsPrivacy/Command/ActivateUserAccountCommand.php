<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

/**
 * @see ActivateUserAccountCommandHandler
 */
final readonly class ActivateUserAccountCommand
{
    public function __construct(
        public int $userStatus,
    ) {
    }
}
