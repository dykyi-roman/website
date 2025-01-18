<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\DeleteUser;

use PHPUnit\Framework\TestCase;
use Profile\User\Application\DeleteUser\Command\DeleteUserAccountCommand;
use Profile\User\DomainModel\Enum\UserId;

final class DeleteUserAccountCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = UserId::fromString('user-123');
        $command = new DeleteUserAccountCommand($userId);

        self::assertEquals($userId, $command->userId);
    }
}
