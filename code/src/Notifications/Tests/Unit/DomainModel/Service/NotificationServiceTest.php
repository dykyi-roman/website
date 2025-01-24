<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

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
use Notifications\DomainModel\Service\NotificationTranslator;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(NotificationService::class)]
final class NotificationServiceTest extends TestCase
{
    private UserNotificationRepositoryInterface&MockObject $userNotificationRepository;
    private NotificationDispatcherInterface&MockObject $notificationDispatcher;
    private NotificationFormatter $notificationFormatter;
    private NotificationRepositoryInterface&MockObject $notificationRepository;
    private TranslatorInterface&MockObject $translator;
    private NotificationTranslator $notificationTranslator;
    private LoggerInterface&MockObject $logger;
    private CacheInterface&MockObject $cacheInterface;
    private NotificationCache $cache;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        $this->userNotificationRepository = $this->createMock(UserNotificationRepositoryInterface::class);
        $this->notificationDispatcher = $this->createMock(NotificationDispatcherInterface::class);
        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cacheInterface = $this->createMock(CacheInterface::class);

        $this->notificationTranslator = new NotificationTranslator($this->translator);
        $this->notificationFormatter = new NotificationFormatter(
            $this->notificationRepository,
            $this->notificationTranslator
        );
        $this->cache = new NotificationCache($this->cacheInterface);

        $this->notificationService = new NotificationService(
            $this->userNotificationRepository,
            $this->notificationDispatcher,
            $this->notificationFormatter,
            $this->logger,
            $this->cache
        );
    }

    public function testCreateNotification(): void
    {
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userId = new UserId();
        $notification = new Notification(
            NotificationId::HAPPY_NEW_YEAR,
            NotificationType::SYSTEM,
            new TranslatableText('notification.title'),
            new TranslatableText('notification.message'),
            'test-icon'
        );

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

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($notification);

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Translated Text');

        $this->notificationDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($userId, $this->isType('array'));

        $this->notificationService->createUserNotification($notificationId, $userId);
    }

    public function testCreateNotificationHandlesDispatcherException(): void
    {
        $notificationId = NotificationId::HAPPY_BIRTHDAY;
        $userId = new UserId();
        $notification = new Notification(
            NotificationId::HAPPY_BIRTHDAY,
            NotificationType::SYSTEM,
            new TranslatableText('notification.title'),
            new TranslatableText('notification.message'),
            'test-icon'
        );
        $exception = new \RuntimeException('Dispatch error');

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

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($notification);

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Translated Text');

        $this->notificationDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->notificationService->createUserNotification($notificationId, $userId);
    }

    public function testMarkAsRead(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

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
    }

    public function testMarkAsReadSkipsWhenAlreadyRead(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );
        $userNotification->setIsRead();

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
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

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
    }

    public function testMarkAsDeletedSkipsWhenAlreadyDeleted(): void
    {
        $userId = new UserId();
        $userNotificationId = new UserNotificationId();
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );
        $userNotification->setIsDelete();

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

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(5);

        $this->userNotificationRepository
            ->expects($this->never())
            ->method('getUnreadCount');

        $result = $this->notificationService->getUnreadCount($userId);
        $this->assertSame(5, $result);
    }

    public function testGetUnreadCountFromRepository(): void
    {
        $userId = new UserId();

        $this->cacheInterface
            ->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUnreadCount')
            ->with($userId)
            ->willReturn(3);

        $result = $this->notificationService->getUnreadCount($userId);
        $this->assertSame(3, $result);
    }

    public function testGetUserNotifications(): void
    {
        $userId = new UserId();
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotificationId = new UserNotificationId();
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

        $notification = new Notification(
            $notificationId,
            NotificationType::SYSTEM,
            new TranslatableText('notification.title'),
            new TranslatableText('notification.message'),
            'test-icon'
        );

        $paginationDto = new PaginationDto([$userNotification], 1, 20);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUserNotifications')
            ->with($userId, 1, 20)
            ->willReturn($paginationDto);

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($notificationId)
            ->willReturn($notification);

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('Translated Text');

        $result = $this->notificationService->getUserNotifications($userId);
        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertArrayHasKey('type', $result->items[0]);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
    }

    public function testGetUserNotificationsHandlesNotFoundNotification(): void
    {
        $userId = new UserId();
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userNotificationId = new UserNotificationId();
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

        $paginationDto = new PaginationDto([$userNotification], 1, 20);

        $this->userNotificationRepository
            ->expects($this->once())
            ->method('getUserNotifications')
            ->with($userId, 1, 20)
            ->willReturn($paginationDto);

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->willThrowException(new NotificationNotFoundException(NotificationId::HAPPY_NEW_YEAR));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Notification not found by Id: HAPPY_NEW_YEAR');

        $result = $this->notificationService->getUserNotifications($userId);
        $this->assertInstanceOf(PaginationDto::class, $result);
        $this->assertEmpty($result->items);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
    }
}
