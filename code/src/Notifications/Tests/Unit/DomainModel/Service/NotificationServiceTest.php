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
use Notifications\DomainModel\Service\NotificationTranslatorInterface;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(NotificationService::class)]
final class NotificationServiceTest extends TestCase
{
    private NotificationRepositoryInterface&MockObject $notificationRepository;
    private UserNotificationRepositoryInterface&MockObject $userNotificationRepository;
    private NotificationDispatcherInterface&MockObject $notificationDispatcher;
    private NotificationTranslatorInterface&MockObject $notificationTranslator;
    private LoggerInterface&MockObject $logger;
    private CacheInterface&MockObject $cacheInterface;
    private NotificationFormatter $notificationFormatter;
    private NotificationCache $cache;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->userNotificationRepository = $this->createMock(UserNotificationRepositoryInterface::class);
        $this->notificationDispatcher = $this->createMock(NotificationDispatcherInterface::class);
        $this->notificationTranslator = $this->createMock(NotificationTranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cacheInterface = $this->createMock(CacheInterface::class);

        $this->notificationFormatter = new NotificationFormatter($this->notificationTranslator);
        $this->cache = new NotificationCache($this->cacheInterface);

        $this->notificationService = new NotificationService(
            $this->notificationRepository,
            $this->userNotificationRepository,
            $this->notificationDispatcher,
            $this->notificationFormatter,
            $this->logger,
            $this->cache
        );
    }

    /**
     * @return array<string, array{0: Notification}>
     */
    public static function notificationDataProvider(): array
    {
        return [
            'system notification' => [
                new Notification(
                    new NotificationId(),
                    NotificationName::HAPPY_NEW_YEAR,
                    NotificationType::SYSTEM,
                    TranslatableText::create('system.title'),
                    TranslatableText::create('system.message'),
                    'system-icon'
                ),
            ],
            'personal notification' => [
                new Notification(
                    new NotificationId(),
                    NotificationName::HAPPY_BIRTHDAY,
                    NotificationType::PERSONAL,
                    TranslatableText::create('user.title'),
                    TranslatableText::create('user.message'),
                    'user-icon'
                ),
            ],
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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(0);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->willReturn($transformedData);

        $this->notificationDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($userId, $this->isType('array'));

        $this->notificationService->createUserNotification($notification, $userId);
    }

    public function testCreateUserNotificationHandlesDispatcherException(): void
    {
        $notification = new Notification(
            new NotificationId(),
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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(0);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
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
            new NotificationId(),
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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(1);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

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

        $this->cacheInterface
            ->expects($this->never())
            ->method('get');

        $this->cacheInterface
            ->expects($this->never())
            ->method('set');

        $this->notificationService->markAsRead($userId, $userNotificationId);
    }

    public function testMarkAllAsRead(): void
    {
        $userId = new UserId();

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('markAllAsRead')
            ->with($userId);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(1);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

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

        $this->cacheInterface
            ->expects($this->never())
            ->method('get');

        $this->cacheInterface
            ->expects($this->never())
            ->method('set');

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

        $this->cacheInterface
            ->expects($this->never())
            ->method('get');

        $this->cacheInterface
            ->expects($this->never())
            ->method('set');

        $this->notificationService->markAsDeleted($userId, $userNotificationId);
    }

    public function testMarkAllAsDeleted(): void
    {
        $userId = new UserId();

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('markAllAsDeleted')
            ->with($userId);

        $this->cacheInterface
            ->expects($this->once())
            ->method('set');

        $this->notificationService->markAllAsDeleted($userId);
    }

    public function testGetUnreadCountFromCache(): void
    {
        $userId = new UserId();
        $expectedCount = 5;

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
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

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->willReturn($transformedData);

        $result = $this->notificationService->getUserNotifications($userId, $page, $perPage);

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertNotEmpty($result->items);
        $this->assertArrayHasKey('type', $result->items[0]);
        $this->assertSame($page, $result->page);
        $this->assertSame($perPage, $result->limit);
    }

    public function testGetUserNotificationsHandlesNotFoundNotification(): void
    {
        $userId = new UserId();
        $userNotification = $this->createUnreadNotification(new UserNotificationId(), $userId);
        $paginationDto = new PaginationDto([$userNotification], 1, 20);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUserNotifications')
            ->willReturn($paginationDto);

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->willThrowException(new NotificationNotFoundException(new NotificationId()));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->isType('string'));

        $result = $this->notificationService->getUserNotifications($userId);

        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertEmpty($result->items);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
    }

    private function createUnreadNotification(UserNotificationId $id, UserId $userId): UserNotification
    {
        $notification = new Notification(
            new NotificationId(),
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
