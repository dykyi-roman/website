<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Notifications\DomainModel\Service\NotificationCache;
use Notifications\DomainModel\Service\NotificationDispatcherInterface;
use Notifications\DomainModel\Service\NotificationFormatter;
use Notifications\DomainModel\Service\NotificationService;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(NotificationService::class)]
final class NotificationServiceTest extends TestCase
{
    private NotificationRepositoryInterface&MockObject $notificationRepository;
    private UserNotificationRepositoryInterface&MockObject $userNotificationRepository;
    private NotificationDispatcherInterface&MockObject $notificationDispatcher;
    private NotificationFormatter&MockObject $notificationFormatter;
    private LoggerInterface&MockObject $logger;
    private NotificationCache&MockObject $cache;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->userNotificationRepository = $this->createMock(UserNotificationRepositoryInterface::class);
        $this->notificationDispatcher = $this->createMock(NotificationDispatcherInterface::class);
        $this->notificationFormatter = $this->createMock(NotificationFormatter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(NotificationCache::class);

        $this->notificationService = new NotificationService(
            $this->notificationRepository,
            $this->userNotificationRepository,
            $this->notificationDispatcher,
            $this->notificationFormatter,
            $this->logger,
            $this->cache
        );
    }

    public static function notificationDataProvider(): array
    {
        return [
            'system notification' => [
                new Notification(
                    new NotificationId('test-id-1'),
                    NotificationName::HAPPY_NEW_YEAR,
                    NotificationType::SYSTEM,
                    TranslatableText::create('system.title'),
                    TranslatableText::create('system.message'),
                    'system-icon'
                )
            ],
            'personal notification' => [
                new Notification(
                    new NotificationId('test-id-2'),
                    NotificationName::HAPPY_BIRTHDAY,
                    NotificationType::PERSONAL,
                    TranslatableText::create('user.title'),
                    TranslatableText::create('user.message'),
                    'user-icon'
                )
            ]
        ];
    }

    #[DataProvider('notificationDataProvider')]
    public function testCreateUserNotification(Notification $notification): void
    {
        $userId = new UserId();
        $transformedData = ['type' => 'test', 'message' => 'test message'];

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(UserNotification::class));

        $this->cache
            ->expects($this->once())
            ->method('incrementUnreadCount')
            ->with($userId);

        $this->notificationFormatter
            ->expects($this->once())
            ->method('transform')
            ->willReturn($transformedData);

        $this->notificationDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($userId, $transformedData);

        $this->notificationService->createUserNotification($notification, $userId);
    }

    public function testCreateUserNotificationHandlesDispatcherException(): void
    {
        $notification = new Notification(
            new NotificationId('test-id'),
            NotificationName::HAPPY_BIRTHDAY,
            NotificationType::SYSTEM,
            TranslatableText::create('test.title'),
            TranslatableText::create('test.message'),
            'test-icon'
        );
        $userId = new UserId();
        $exception = new \RuntimeException('Dispatch error');

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('save');

        $this->cache
            ->expects($this->once())
            ->method('incrementUnreadCount');

        $this->notificationFormatter
            ->expects($this->once())
            ->method('transform')
            ->willReturn([]);

        $this->notificationDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->notificationService->createUserNotification($notification, $userId);
    }

    public function testCreateNotification(): void
    {
        $notification = new Notification(
            new NotificationId('test-id'),
            NotificationName::HAPPY_BIRTHDAY,
            NotificationType::SYSTEM,
            TranslatableText::create('test.title'),
            TranslatableText::create('test.message'),
            'test-icon'
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($notification);

        $this->notificationService->createNotification($notification);
    }

    public function testMarkAsRead(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $userNotification = $this->createUnreadNotification($userNotificationId, $userId);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userNotificationId)
            ->willReturn($userNotification);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($userNotification);

        $this->cache
            ->expects($this->once())
            ->method('decrementUnreadCount')
            ->with($userId);

        $this->notificationService->markAsRead($userId, $userNotificationId);
        $this->assertTrue($userNotification->isRead());
    }

    public function testMarkAsReadSkipsWhenAlreadyRead(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $userNotification = $this->createReadNotification($userNotificationId, $userId);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userNotificationId)
            ->willReturn($userNotification);

        $this->userNotificationRepository
            ->expects($this->never())
            ->method('save');

        $this->cache
            ->expects($this->never())
            ->method('decrementUnreadCount');

        $this->notificationService->markAsRead($userId, $userNotificationId);
    }

    public function testMarkAllAsRead(): void
    {
        $userId = new UserId();

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('markAllAsRead')
            ->with($userId);

        $this->cache
            ->expects($this->once())
            ->method('resetUnreadCount')
            ->with($userId);

        $this->notificationService->markAllAsRead($userId);
    }

    public function testMarkAsDeleted(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $userNotification = $this->createUnreadNotification($userNotificationId, $userId);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userNotificationId)
            ->willReturn($userNotification);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($userNotification);

        $this->cache
            ->expects($this->once())
            ->method('decrementUnreadCount')
            ->with($userId);

        $this->notificationService->markAsDeleted($userId, $userNotificationId);
        $this->assertTrue($userNotification->isDeleted());
    }

    public function testMarkAsDeletedWithReadNotification(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $userNotification = $this->createReadNotification($userNotificationId, $userId);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userNotificationId)
            ->willReturn($userNotification);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('save')
            ->with($userNotification);

        $this->cache
            ->expects($this->never())
            ->method('decrementUnreadCount');

        $this->notificationService->markAsDeleted($userId, $userNotificationId);
        $this->assertTrue($userNotification->isDeleted());
    }

    public function testMarkAsDeletedSkipsWhenAlreadyDeleted(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $userNotification = $this->createDeletedNotification($userNotificationId, $userId);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userNotificationId)
            ->willReturn($userNotification);

        $this->userNotificationRepository
            ->expects($this->never())
            ->method('save');

        $this->cache
            ->expects($this->never())
            ->method('decrementUnreadCount');

        $this->notificationService->markAsDeleted($userId, $userNotificationId);
    }

    public function testMarkAllAsDeleted(): void
    {
        $userId = new UserId();

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('markAllAsDeleted')
            ->with($userId);

        $this->cache
            ->expects($this->once())
            ->method('resetUnreadCount')
            ->with($userId);

        $this->notificationService->markAllAsDeleted($userId);
    }

    public function testGetUnreadCountFromCache(): void
    {
        $userId = new UserId();
        $expectedCount = 5;

        $this->cache
            ->expects($this->once())
            ->method('getUnreadCount')
            ->with($userId)
            ->willReturn($expectedCount);

        $this->userNotificationRepository
            ->expects($this->never())
            ->method('getUnreadCount');

        $result = $this->notificationService->getUnreadCount($userId);
        $this->assertSame($expectedCount, $result);
    }

    public function testGetUnreadCountFromRepository(): void
    {
        $userId = new UserId();
        $expectedCount = 3;

        $this->cache
            ->expects($this->once())
            ->method('getUnreadCount')
            ->with($userId)
            ->willReturn(null);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUnreadCount')
            ->with($userId)
            ->willReturn($expectedCount);

        $result = $this->notificationService->getUnreadCount($userId);
        $this->assertSame($expectedCount, $result);
    }

    public function testGetUserNotifications(): void
    {
        $userId = new UserId();
        $page = 2;
        $perPage = 15;
        $userNotification = $this->createUnreadNotification(new UserNotificationId(), $userId);
        $paginationDto = new PaginationDto([$userNotification], $page, $perPage);
        $transformedData = ['type' => 'test', 'message' => 'test message'];

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUserNotifications')
            ->with($userId, $page, $perPage)
            ->willReturn($paginationDto);

        $this->notificationFormatter
            ->expects($this->once())
            ->method('transform')
            ->with($userNotification)
            ->willReturn($transformedData);

        $result = $this->notificationService->getUserNotifications($userId, $page, $perPage);
        
        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertSame([$transformedData], $result->items);
        $this->assertSame($page, $result->page);
        $this->assertSame($perPage, $result->limit);
    }

    public function testGetUserNotificationsHandlesNotFoundNotification(): void
    {
        $userId = new UserId();
        $userNotification = $this->createUnreadNotification(new UserNotificationId(), $userId);
        $paginationDto = new PaginationDto([$userNotification], 1, 20);
        $exception = new NotificationNotFoundException(new NotificationId('test-id'));

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUserNotifications')
            ->willReturn($paginationDto);

        $this->notificationFormatter
            ->expects($this->once())
            ->method('transform')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $result = $this->notificationService->getUserNotifications($userId);
        
        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertEmpty($result->items);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
    }

    private function createUnreadNotification(UserNotificationId $id, UserId $userId): UserNotification
    {
        $notification = new Notification(
            new NotificationId('test-id'),
            NotificationName::HAPPY_BIRTHDAY,
            NotificationType::SYSTEM,
            TranslatableText::create('test.title'),
            TranslatableText::create('test.message'),
            'test-icon'
        );
        return new UserNotification($id, $notification, $userId);
    }

    private function createReadNotification(UserNotificationId $id, UserId $userId): UserNotification
    {
        $notification = $this->createUnreadNotification($id, $userId);
        $notification->setIsRead();
        return $notification;
    }

    private function createDeletedNotification(UserNotificationId $id, UserId $userId): UserNotification
    {
        $notification = $this->createUnreadNotification($id, $userId);
        $notification->setIsDelete();
        return $notification;
    }
}
