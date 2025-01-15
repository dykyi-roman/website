<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\Notification\Symfony\Transport;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Notification\Symfony\Transport\CustomEmailTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Notification\Notification;

#[CoversClass(CustomEmailTransport::class)]
final class CustomEmailTransportTest extends TestCase
{
    /** @var MailerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private MailerInterface $mailer;
    private CustomEmailTransport $transport;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->transport = new CustomEmailTransport($this->mailer);
        $this->transport->from = 'from@example.com';
    }

    public function testSupportsOnlyChatMessage(): void
    {
        /** @var ChatMessage&\PHPUnit\Framework\MockObject\MockObject $chatMessage */
        $chatMessage = $this->createMock(ChatMessage::class);
        self::assertTrue($this->transport->supports($chatMessage));

        /** @var MessageInterface&\PHPUnit\Framework\MockObject\MockObject $otherMessage */
        $otherMessage = $this->createMock(MessageInterface::class);
        self::assertFalse($this->transport->supports($otherMessage));
    }

    public function testSendEmail(): void
    {
        /** @var Notification&\PHPUnit\Framework\MockObject\MockObject $notification */
        $notification = $this->createMock(Notification::class);
        $notification->method('getSubject')->willReturn('Test Subject');
        $notification->method('getContent')->willReturn('Test Content');

        /** @var ChatMessage&\PHPUnit\Framework\MockObject\MockObject $message */
        $message = $this->createMock(ChatMessage::class);
        $message->method('getNotification')->willReturn($notification);
        $message->method('getRecipientId')->willReturn('test@example.com');

        $sendCount = 0;
        $this->mailer->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email) use (&$sendCount): void {
                ++$sendCount;
                self::assertSame('from@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('test@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Test Subject', $email->getSubject());
                self::assertSame('Test Content', $email->getHtmlBody());
            });

        $sentMessage = $this->transport->send($message);
        self::assertStringContainsString('custom-email://', (string) $sentMessage->getTransport());
        self::assertSame(1, $sendCount, 'Send method should be called exactly once');
    }

    public function testSendWithInvalidMessageType(): void
    {
        /** @var MessageInterface&\PHPUnit\Framework\MockObject\MockObject $message */
        $message = $this->createMock(MessageInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "'.CustomEmailTransport::class.'" transport only supports instances of "'.ChatMessage::class);

        $this->transport->send($message);
    }

    public function testSendWithNullNotification(): void
    {
        /** @var ChatMessage&\PHPUnit\Framework\MockObject\MockObject $message */
        $message = $this->createMock(ChatMessage::class);
        $message->method('getNotification')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Notification cannot be null');

        $this->transport->send($message);
    }

    public function testSendWithNullRecipientId(): void
    {
        /** @var Notification&\PHPUnit\Framework\MockObject\MockObject $notification */
        $notification = $this->createMock(Notification::class);

        /** @var ChatMessage&\PHPUnit\Framework\MockObject\MockObject $message */
        $message = $this->createMock(ChatMessage::class);
        $message->method('getNotification')->willReturn($notification);
        $message->method('getRecipientId')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Recipient ID cannot be null');

        $this->transport->send($message);
    }

    public function testToString(): void
    {
        self::assertStringContainsString('custom-email://', (string) $this->transport);
    }
}
