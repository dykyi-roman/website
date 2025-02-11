<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserVerification\Command;

use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserVerification\Command\VerifyUserEmailCommand;
use Shared\DomainModel\ValueObject\UserId;

final class VerifyUserEmailCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new VerifyUserEmailCommand($userId);

        $this->assertSame($userId, $command->userId);
    }
}
