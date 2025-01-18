<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\Notifications\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\Notifications\Query\UsersNotificationQuery;
use Profile\User\Application\Notifications\Query\UsersNotificationQueryHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;

#[CoversClass(UsersNotificationQueryHandler::class)]
final class UsersNotificationQueryHandlerTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private UsersNotificationQueryHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new UsersNotificationQueryHandler($this->userRepository);
    }

    public function testHandleReturnsAllUsers(): void
    {
        $expectedUsers = [
            new UserId(),
            new UserId(),
            new UserId(),
        ];

        $this->userRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn($expectedUsers);

        $query = new UsersNotificationQuery();
        $result = $this->handler->__invoke($query);

        self::assertSame($expectedUsers, $result);
        self::assertCount(3, $result);
        foreach ($result as $userId) {
            self::assertInstanceOf(UserId::class, $userId);
        }
    }

    public function testHandleReturnsEmptyArrayWhenNoUsers(): void
    {
        $this->userRepository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([]);

        $query = new UsersNotificationQuery();
        $result = $this->handler->__invoke($query);

        self::assertSame([], $result);
        self::assertCount(0, $result);
    }
}
