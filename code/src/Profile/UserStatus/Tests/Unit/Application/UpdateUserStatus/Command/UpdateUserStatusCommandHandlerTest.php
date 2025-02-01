<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\Application\UpdateUserStatus\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\Application\UpdateUserStatus\Command\UpdateUserStatusCommand;
use Profile\UserStatus\Application\UpdateUserStatus\Command\UpdateUserStatusCommandHandler;
use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UpdateUserStatusCommandHandler::class)]
final class UpdateUserStatusCommandHandlerTest extends TestCase
{
    private const string VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const string VALID_UUID_2 = '550e8400-e29b-41d4-a716-446655440001';

    /** @var UserStatusRepositoryInterface&MockObject */
    private MockObject $userStatusRepository;

    /** @var MessageBusInterface&MockObject */
    private MockObject $eventBus;

    private UpdateUserStatusCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userStatusRepository = $this->createMock(UserStatusRepositoryInterface::class);
        $this->eventBus = $this->createMock(MessageBusInterface::class);
        $this->handler = new UpdateUserStatusCommandHandler(
            $this->userStatusRepository,
            $this->eventBus
        );
    }

    public function testInvokeSuccessfullyUpdatesStatuses(): void
    {
        $lastOnlineAt = new \DateTimeImmutable();
        $items = [
            [
                'user_id' => self::VALID_UUID,
                'is_online' => true,
                'last_online_at' => $lastOnlineAt->format('c'),
            ],
            [
                'user_id' => self::VALID_UUID_2,
                'is_online' => false,
                'last_online_at' => $lastOnlineAt->format('c'),
            ],
        ];

        $command = new UpdateUserStatusCommand($items);

        $expectedStatuses = [
            new UserStatus(new UserId(self::VALID_UUID), true, $lastOnlineAt),
            new UserStatus(new UserId(self::VALID_UUID_2), false, $lastOnlineAt),
        ];

        // Assert repository saves the statuses
        $this->userStatusRepository->expects($this->once())
            ->method('saveOrUpdate')
            ->with($this->callback(function (UserStatus ...$statuses) use ($lastOnlineAt) {
                $this->assertCount(2, $statuses);

                $status1 = $statuses[0];
                $status2 = $statuses[1];

                $this->assertEquals(self::VALID_UUID, $status1->getUserId()->toRfc4122());
                $this->assertTrue($status1->isOnline());
                $this->assertEquals(
                    $lastOnlineAt->format('Y-m-d H:i:s'),
                    $status1->getLastOnlineAt()->format('Y-m-d H:i:s')
                );

                $this->assertEquals(self::VALID_UUID_2, $status2->getUserId()->toRfc4122());
                $this->assertFalse($status2->isOnline());
                $this->assertEquals(
                    $lastOnlineAt->format('Y-m-d H:i:s'),
                    $status2->getLastOnlineAt()->format('Y-m-d H:i:s')
                );

                return true;
            }));

        // Assert event is dispatched for offline user
        $this->eventBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (UserWentOnlineEvent $event) {
                $this->assertEquals(self::VALID_UUID_2, $event->userId->toRfc4122());

                return true;
            }));

        // Execute handler
        ($this->handler)($command);

        // Add assertion to mark test as not risky
        $this->assertTrue(true);
    }

    public function testInvokeThrowsExceptionForInvalidItemFormat(): void
    {
        $items = [
            'not_an_array', // Invalid item
        ];

        $command = new UpdateUserStatusCommand($items);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item must be an array');

        ($this->handler)($command);
    }

    public function testInvokeThrowsExceptionForMissingRequiredKeys(): void
    {
        $items = [
            [
                'user_id' => self::VALID_UUID,
                // missing is_online
            ],
        ];

        $command = new UpdateUserStatusCommand($items);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item must contain is_online and user_id keys');

        ($this->handler)($command);
    }

    public function testInvokeThrowsExceptionForInvalidUserIdType(): void
    {
        $items = [
            [
                'user_id' => 123, // Invalid type
                'is_online' => true,
            ],
        ];

        $command = new UpdateUserStatusCommand($items);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('user_id must be a string');

        ($this->handler)($command);
    }
}
