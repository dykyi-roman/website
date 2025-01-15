<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommand;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(DeleteUserAccountCommand::class)]
final class DeleteUserAccountCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();

        $command = new DeleteUserAccountCommand(
            userId: $userId
        );

        self::assertSame($userId, $command->userId);
    }
}
