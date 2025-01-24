<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Service\NotificationTranslator;
use Notifications\DomainModel\ValueObject\NotificationId;
use Notifications\DomainModel\ValueObject\TranslatableText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(NotificationTranslator::class)]
final class NotificationTranslatorTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private NotificationTranslator $notificationTranslator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->notificationTranslator = new NotificationTranslator($this->translator);
    }

    public function testTranslateNotification(): void
    {
        $type = NotificationType::INFORMATION;
        $title = TranslatableText::create('title.id', ['param1' => 'value1']);
        $message = TranslatableText::create('message.id', ['param2' => 'value2']);

        $notification = new Notification(
            new NotificationId(),
            NotificationName::HAPPY_NEW_YEAR,
            $type,
            $title,
            $message,
            'test-icon'
        );

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['title.id', ['param1' => 'value1'], null, null, 'Translated Title'],
                ['message.id', ['param2' => 'value2'], null, null, 'Translated Message'],
            ]);

        $result = $this->notificationTranslator->translateNotification($notification);

        $expectedResult = [
            'type' => 'INFORMATION',
            'title' => 'Translated Title',
            'message' => 'Translated Message',
            'icon' => 'test-icon',
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testTranslateNotificationWithNullIcon(): void
    {
        $type = NotificationType::INFORMATION;
        $title = TranslatableText::create('title.id');
        $message = TranslatableText::create('message.id');

        $notification = new Notification(
            new NotificationId(),
            NotificationName::HAPPY_BIRTHDAY,
            $type,
            $title,
            $message,
            null
        );

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['title.id', [], null, null, 'Translated Title'],
                ['message.id', [], null, null, 'Translated Message'],
            ]);

        $result = $this->notificationTranslator->translateNotification($notification);

        $expectedResult = [
            'type' => 'INFORMATION',
            'title' => 'Translated Title',
            'message' => 'Translated Message',
            'icon' => null,
        ];

        $this->assertSame($expectedResult, $result);
    }

    public function testTranslateNotificationWithComplexParameters(): void
    {
        $type = NotificationType::INFORMATION;
        $titleParams = [
            'user' => 'John',
            'count' => 5,
            'date' => '2024-01-01',
        ];
        $messageParams = [
            'items' => 'item1, item2',  // Convert array to string
            'total' => 100.50,
            'status' => true,
        ];
        $title = TranslatableText::create('title.complex', $titleParams);
        $message = TranslatableText::create('message.complex', $messageParams);

        $notification = new Notification(
            new NotificationId(),
            NotificationName::PASS_VERIFICATION,
            $type,
            $title,
            $message,
            'test-icon'
        );

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['title.complex', $titleParams, null, null, 'Title with John, 5 items on 2024-01-01'],
                ['message.complex', $messageParams, null, null, 'Message with 2 items, total: 100.50'],
            ]);

        $result = $this->notificationTranslator->translateNotification($notification);

        $expectedResult = [
            'type' => 'INFORMATION',
            'title' => 'Title with John, 5 items on 2024-01-01',
            'message' => 'Message with 2 items, total: 100.50',
            'icon' => 'test-icon',
        ];

        $this->assertSame($expectedResult, $result);
    }
}
