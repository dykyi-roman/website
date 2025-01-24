<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsAccount\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsAccount\Command\VerifyCodeCommand;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(VerifyCodeCommand::class)]
final class VerifyCodeCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $command = new VerifyCodeCommand(
            userId: $userId,
            type: 'email',
            code: '123456'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('email', $command->type);
        self::assertSame('123456', $command->code);
    }

    public function testCreateCommandWithPhoneVerification(): void
    {
        $userId = new UserId();
        $command = new VerifyCodeCommand(
            userId: $userId,
            type: 'phone',
            code: '654321'
        );

        self::assertSame($userId, $command->userId);
        self::assertSame('phone', $command->type);
        self::assertSame('654321', $command->code);
    }
}
