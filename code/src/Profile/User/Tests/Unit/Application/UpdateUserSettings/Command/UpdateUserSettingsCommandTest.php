<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UpdateUserSettings\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UpdateUserSettings\Command\UpdateUserSettingsCommand;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(UpdateUserSettingsCommand::class)]
final class UpdateUserSettingsCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'john@example.com',
            phone: '+1234567890',
            avatar: 'avatar.jpg'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('John Doe', $command->name);
        self::assertSame('john@example.com', $command->email);
        self::assertSame('+1234567890', $command->phone);
        self::assertSame('avatar.jpg', $command->avatar);
    }

    public function testCreateCommandWithoutAvatar(): void
    {
        $userId = new UserId();
        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'john@example.com',
            phone: '+1234567890'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('John Doe', $command->name);
        self::assertSame('john@example.com', $command->email);
        self::assertSame('+1234567890', $command->phone);
        self::assertNull($command->avatar);
    }
}
