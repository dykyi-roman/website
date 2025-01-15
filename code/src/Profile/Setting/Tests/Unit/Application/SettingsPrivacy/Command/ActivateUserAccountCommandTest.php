<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommand;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(ActivateUserAccountCommand::class)]
final class ActivateUserAccountCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $userStatus = 1;

        $command = new ActivateUserAccountCommand(
            userId: $userId,
            userStatus: $userStatus
        );

        self::assertSame($userId, $command->userId);
        self::assertSame($userStatus, $command->userStatus);
    }
}
