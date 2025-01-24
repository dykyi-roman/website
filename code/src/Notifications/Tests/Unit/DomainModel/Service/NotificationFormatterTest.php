<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\UserNotification;
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
    private NotificationTranslatorInterface&MockObject $notificationTranslator;
    private NotificationFormatter $notificationFormatter;

    protected function setUp(): void
    {
        $this->notificationTranslator = $this->createMock(NotificationTranslatorInterface::class);
        $this->notificationFormatter = new NotificationFormatter(
            $this->notificationTranslator
        );
    }

    public function testTransformWithAllFields(): void
    {
        $notificationId = new NotificationId();
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');

        $notification = new Notification(
            $notificationId,
            NotificationName::HAPPY_BIRTHDAY,
            NotificationType::SYSTEM,
            TranslatableText::create('notification.title'),
            TranslatableText::create('notification.message'),
            'icon-name'
        );

        $userNotification = new UserNotification(
            $userNotificationId,
            $notification,
            $userId
        );

        // Set read and delete times for testing
        $userNotification->setIsRead();
        $userNotification->setIsDelete();

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
        $notificationId = new NotificationId();
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');

        $notification = new Notification(
            $notificationId,
            NotificationName::PASS_VERIFICATION,
            NotificationType::SYSTEM,
            TranslatableText::create('notification.title'),
            TranslatableText::create('notification.message'),
            'icon-name'
        );

        $userNotification = new UserNotification(
            $userNotificationId,
            $notification,
            $userId
        );

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

    public function testTransformThrowsExceptionWhenTranslatorFails(): void
    {
        $notificationId = new NotificationId();
        $notification = new Notification(
            $notificationId,
            NotificationName::PASS_VERIFICATION,
            NotificationType::SYSTEM,
            TranslatableText::create('notification.title'),
            TranslatableText::create('notification.message'),
            'icon-name'
        );

        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $userNotificationId = new UserNotificationId('660e8400-e29b-41d4-a716-446655440000');
        $userNotification = new UserNotification(
            $userNotificationId,
            $notification,
            $userId
        );

        $this->notificationTranslator
            ->expects($this->once())
            ->method('translateNotification')
            ->with($notification)
            ->willThrowException(new NotificationNotFoundException($notificationId));

        $this->expectException(NotificationNotFoundException::class);
        $this->notificationFormatter->transform($userNotification);
    }
}
