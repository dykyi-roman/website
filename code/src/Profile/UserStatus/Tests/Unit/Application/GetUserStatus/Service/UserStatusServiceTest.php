<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\Application\GetUserStatus\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\Application\GetUserStatus\Service\UserStatusService;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UserStatusService::class)]
final class UserStatusServiceTest extends TestCase
{
    private const string VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const string VALID_UUID_2 = '550e8400-e29b-41d4-a716-446655440001';

    /** @var UserStatusInterface&MockObject */
    private MockObject $userStatus;

    private UserStatusService $service;

    protected function setUp(): void
    {
        $this->userStatus = $this->createMock(UserStatusInterface::class);
        $this->service = new UserStatusService($this->userStatus);
    }

    public function testGetAllUserStatusesReturnsStatuses(): void
    {
        $lastOnlineAt = new \DateTimeImmutable();
        $statuses = [
            new UserUpdateStatus(new UserId(self::VALID_UUID), true, $lastOnlineAt),
            new UserUpdateStatus(new UserId(self::VALID_UUID_2), false, $lastOnlineAt),
        ];

        $this->userStatus->expects($this->once())
            ->method('getAllUserStatuses')
            ->willReturn($statuses);

        $result = $this->service->getAllUserStatuses();

        $this->assertCount(2, $result);
        $this->assertEquals($statuses, $result);
    }

    public function testGetAllUserStatusesReturnsEmptyArray(): void
    {
        $this->userStatus->expects($this->once())
            ->method('getAllUserStatuses')
            ->willReturn([]);

        $result = $this->service->getAllUserStatuses();

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }
}
