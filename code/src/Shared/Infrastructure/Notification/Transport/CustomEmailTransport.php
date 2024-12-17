<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification\Transport;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CustomEmailTransport extends AbstractTransport
{
    public string $from = '';

    public function __construct(
        private readonly MailerInterface $mailer,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('custom-email://%s', $this->getEndpoint());
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof ChatMessage) {
            throw new \InvalidArgumentException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, ChatMessage::class, get_class($message)));
        }

        $notification = $message->getNotification();

        $email = (new Email())
            ->from($this->from)
            ->to($message->getRecipientId())
            ->subject($notification->getSubject())
            ->html($notification->getContent());

        $this->mailer->send($email);

        return new SentMessage($message, (string) $this);
    }
}
