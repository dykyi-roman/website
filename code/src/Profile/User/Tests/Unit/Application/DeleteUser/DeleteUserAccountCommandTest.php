<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\DeleteUser;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\DeleteUser\Command\DeleteUserAccountCommand;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(DeleteUserAccountCommand::class)]
final class DeleteUserAccountCommandTest extends TestCase
{
    public function testCreateCommand(): void
    {
        $userId = new UserId();
        $command = new DeleteUserAccountCommand($userId);

        self::assertEquals($userId, $command->userId);
    }
}
