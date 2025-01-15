<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\Notification\Symfony\Channel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Notification\Symfony\Channel\CustomEmailChannel;
use Shared\Infrastructure\Notification\Symfony\Channel\CustomEmailOptions;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @method MockObject createMock(string $originalClassName)
 */
#[CoversClass(CustomEmailChannel::class)]
final class CustomEmailChannelTest extends TestCase
{
    /** @var TransportInterface&MockObject */
    private TransportInterface $transport;
    private CustomEmailChannel $channel;
    private const FROM_EMAIL = 'from@example.com';

    protected function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->channel = new CustomEmailChannel($this->transport, self::FROM_EMAIL);
    }

    public function testSupportsEmailRecipient(): void
    {
        /** @var Notification&MockObject $notification */
        $notification = $this->createMock(Notification::class);
        /** @var EmailRecipientInterface&MockObject $recipient */
        $recipient = $this->createMock(EmailRecipientInterface::class);

        self::assertTrue($this->channel->supports($notification, $recipient));
    }

    public function testDoesNotSupportNonEmailRecipient(): void
    {
        /** @var Notification&MockObject $notification */
        $notification = $this->createMock(Notification::class);
        /** @var RecipientInterface&MockObject $recipient */
        $recipient = $this->createMock(RecipientInterface::class);

        self::assertFalse($this->channel->supports($notification, $recipient));
    }

    public function testNotifyWithEmailRecipient(): void
    {
        /** @var Notification&MockObject $notification */
        $notification = $this->createMock(Notification::class);
        $notification->method('getSubject')->willReturn('Test Subject');

        /** @var EmailRecipientInterface&MockObject $recipient */
        $recipient = $this->createMock(EmailRecipientInterface::class);
        $recipient->method('getEmail')->willReturn('test@example.com');

        $this->transport->expects(self::once())
            ->method('send')
            ->with(self::callback(function (ChatMessage $message) {
                $options = $message->getOptions();

                return $options instanceof CustomEmailOptions
                    && 'test@example.com' === $options->getRecipientId()
                    && 'Test Subject' === $message->getSubject();
            }));

        $this->channel->notify($notification, $recipient);
    }

    public function testNotifyWithNonEmailRecipient(): void
    {
        /** @var Notification&MockObject $notification */
        $notification = $this->createMock(Notification::class);
        /** @var RecipientInterface&MockObject $recipient */
        $recipient = $this->createMock(RecipientInterface::class);

        $this->transport->expects(self::never())->method('send');

        $this->channel->notify($notification, $recipient);
    }
}
