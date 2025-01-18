<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\ChangeUserStatus;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\ChangeUserStatus\ActivateUserAccountCommand;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Enum\UserStatus;

#[CoversClass(ActivateUserAccountCommand::class)]
final class ActivateUserAccountCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $userStatus = UserStatus::ACTIVE;

        $command = new ActivateUserAccountCommand(
            userId: $userId,
            userStatus: $userStatus
        );

        self::assertSame($userId, $command->userId);
        self::assertSame($userStatus, $command->userStatus);
    }
}
