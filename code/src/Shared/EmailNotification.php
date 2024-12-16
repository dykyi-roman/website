<?php

declare(strict_types=1);

namespace App\Shared;

use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\RecipientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Message\EmailMessage;
//use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
//use Symfony\Component\Notifier\Recipient\RecipientInterface;

final readonly class EmailNotification implements NotificationInterface
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        foreach ($recipients as $recipient) {
            if (!$recipient->getEmail()) {
                continue;
            }

            $email = (new Email())
                ->from('noreply@example.com')
                ->to($recipient->getEmail())
                ->subject($notification->subject)
                ->text($notification->content);

            $this->mailer->send($email);
        }
    }
}
