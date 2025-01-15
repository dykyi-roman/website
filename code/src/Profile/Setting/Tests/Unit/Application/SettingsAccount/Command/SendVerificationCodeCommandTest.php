<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsAccount\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsAccount\Command\SendVerificationCodeCommand;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(SendVerificationCodeCommand::class)]
final class SendVerificationCodeCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $command = new SendVerificationCodeCommand(
            userId: $userId,
            type: 'email',
            recipient: 'test@example.com'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('email', $command->type);
        self::assertSame('test@example.com', $command->recipient);
    }

    public function testCreateCommandWithPhoneNumber(): void
    {
        $userId = new UserId();
        $command = new SendVerificationCodeCommand(
            userId: $userId,
            type: 'phone',
            recipient: '+1234567890'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('phone', $command->type);
        self::assertSame('+1234567890', $command->recipient);
    }
}
