<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Notification\Symfony\Channel;

use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Notifier\Transport\TransportInterface;

final readonly class CustomEmailChannel implements ChannelInterface
{
    public function __construct(
        private TransportInterface $transport,
        private string $from,
    ) {
    }

    public function notify(Notification $notification, RecipientInterface $recipient, ?string $transportName = null): void
    {
        if (!$recipient instanceof EmailRecipientInterface) {
            return;
        }

        $message = ChatMessage::fromNotification($notification);
        $message->transport($transportName);

        if ($notification instanceof ChatNotificationInterface) {
            $message->subject($notification->getSubject());
        }

        $message->options((new CustomEmailOptions())->recipientId($recipient->getEmail()));

        /* @phpstan-ignore-next-line */
        $this->transport->from = $this->from;
        $this->transport->send($message);
    }

    public function supports(Notification $notification, RecipientInterface $recipient): bool
    {
        return $recipient instanceof EmailRecipientInterface;
    }
}
