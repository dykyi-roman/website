<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\Service\NotificationFormatter;
use Notifications\DomainModel\Service\NotificationTranslatorInterface;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(NotificationFormatter::class)]
final class NotificationFormatterTest extends TestCase
{
    private NotificationRepositoryInterface&MockObject $notificationRepository;
    private NotificationTranslatorInterface&MockObject $notificationTranslator;
    private NotificationFormatter $notificationFormatter;

    protected function setUp(): void
    {
        $this->notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->notificationTranslator = $this->createMock(NotificationTranslatorInterface::class);
        $this->notificationFormatter = new NotificationFormatter(
            $this->notificationRepository,
            $this->notificationTranslator
        );
    }

    public function testTransformWithAllFields(): void
    {
        $notificationId = NotificationId::PASS_VERIFICATION;
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');

        $notification = new Notification(
            $notificationId,
            NotificationType::SYSTEM,
            new TranslatableText('notification.title'),
            new TranslatableText('notification.message'),
            'icon-name'
        );

        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

        // Set read and delete times for testing
        $userNotification->setIsRead();
        $userNotification->setIsDelete();

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($notificationId)
            ->willReturn($notification);

        $translatedData = [
            'type' => 'test_type',
            'message' => 'Test message',
        ];

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->with($notification)
            ->willReturn($translatedData);

        $result = $this->notificationFormatter->transform($userNotification);

        $expectedResult = [
            'type' => 'test_type',
            'message' => 'Test message',
            'id' => '660e8400-e29b-41d4-a716-446655440000',
            'readAt' => $userNotification->getReadAt()?->format(\DateTimeInterface::ATOM),
            'createdAt' => $userNotification->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'deletedAt' => $userNotification->getDeletedAt()?->format(\DateTimeInterface::ATOM),
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testTransformWithNullOptionalFields(): void
    {
        $notificationId = NotificationId::PASS_VERIFICATION;
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');

        $notification = new Notification(
            $notificationId,
            NotificationType::SYSTEM,
            new TranslatableText('notification.title'),
            new TranslatableText('notification.message'),
            'icon-name'
        );

        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($notificationId)
            ->willReturn($notification);

        $translatedData = [
            'type' => 'test_type',
            'message' => 'Test message',
        ];

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->with($notification)
            ->willReturn($translatedData);

        $result = $this->notificationFormatter->transform($userNotification);

        $expectedResult = [
            'type' => 'test_type',
            'message' => 'Test message',
            'id' => '660e8400-e29b-41d4-a716-446655440000',
            'readAt' => null,
            'createdAt' => $userNotification->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'deletedAt' => null,
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testTransformThrowsExceptionWhenNotificationNotFound(): void
    {
        $notificationId = NotificationId::PASS_VERIFICATION;
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');
        $userNotification = new UserNotification(
            $userNotificationId,
            $notificationId,
            $userId
        );

        $this->notificationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($notificationId)
            ->willThrowException(new NotificationNotFoundException($notificationId));

        $this->expectException(NotificationNotFoundException::class);
        $this->notificationFormatter->transform($userNotification);
    }
}
