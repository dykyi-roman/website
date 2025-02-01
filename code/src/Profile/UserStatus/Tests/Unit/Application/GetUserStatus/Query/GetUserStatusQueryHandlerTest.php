<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\Application\GetUserStatus\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\Application\GetUserStatus\Query\GetUserStatusQuery;
use Profile\UserStatus\Application\GetUserStatus\Query\GetUserStatusQueryHandler;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(GetUserStatusQueryHandler::class)]
final class GetUserStatusQueryHandlerTest extends TestCase
{
    private const string VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';

    /** @var UserStatusInterface&MockObject */
    private MockObject $userStatus;

    /** @var UserStatusRepositoryInterface&MockObject */
    private MockObject $userStatusRepository;

    private GetUserStatusQueryHandler $handler;

    protected function setUp(): void
    {
        $this->userStatus = $this->createMock(UserStatusInterface::class);
        $this->userStatusRepository = $this->createMock(UserStatusRepositoryInterface::class);
        $this->handler = new GetUserStatusQueryHandler($this->userStatus, $this->userStatusRepository);
    }

    public function testInvokeReturnsStatusFromCache(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $query = new GetUserStatusQuery($userId);
        $lastOnlineAt = new \DateTimeImmutable();

        $cachedStatus = new UserUpdateStatus($userId, true, $lastOnlineAt);

        $this->userStatus->expects($this->once())
            ->method('getStatus')
            ->with($userId)
            ->willReturn($cachedStatus);

        // Repository should not be called when cache hit
        $this->userStatusRepository->expects($this->never())
            ->method('findByUserId');

        $result = ($this->handler)($query);

        $this->assertInstanceOf(UserUpdateStatus::class, $result);
        $this->assertEquals($userId->toRfc4122(), $result->userId->toRfc4122());
        $this->assertTrue($result->isOnline);
        $this->assertEquals($lastOnlineAt->format('c'), $result->lastOnlineAt->format('c'));
    }

    public function testInvokeReturnsStatusFromRepository(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $query = new GetUserStatusQuery($userId);
        $lastOnlineAt = new \DateTimeImmutable();

        // Cache miss
        $this->userStatus->expects($this->once())
            ->method('getStatus')
            ->with($userId)
            ->willReturn(null);

        $userStatus = new UserStatus($userId, true, $lastOnlineAt);

        $this->userStatusRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn($userStatus);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(UserUpdateStatus::class, $result);
        $this->assertEquals($userId->toRfc4122(), $result->userId->toRfc4122());
        $this->assertTrue($result->isOnline);
        $this->assertEquals($lastOnlineAt->format('c'), $result->lastOnlineAt->format('c'));
    }

    public function testInvokeReturnsNullWhenStatusNotFound(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $query = new GetUserStatusQuery($userId);

        // Cache miss
        $this->userStatus->expects($this->once())
            ->method('getStatus')
            ->with($userId)
            ->willReturn(null);

        // Repository miss
        $this->userStatusRepository->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn(null);

        $result = ($this->handler)($query);

        $this->assertNull($result);
    }
}
