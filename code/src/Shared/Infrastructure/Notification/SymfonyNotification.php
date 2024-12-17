<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Notification;

use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Notification;
use Symfony\Component\Notifier\Notification\Notification as SymfonyNotificationClass;
use Symfony\Component\Notifier\NotifierInterface;

final readonly class SymfonyNotification implements NotificationInterface
{
    public function __construct(
        private NotifierInterface $notifier,
    ) {
    }

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        $symfonyRecipients = [];
        foreach ($recipients as $recipient) {
            $symfonyRecipients[] = new \Symfony\Component\Notifier\Recipient\Recipient(
                $recipient->getEmail(),
                $recipient->getPhone(),
            );
        }

        $symfonyNotification = new SymfonyNotificationClass(
            $notification->subject,
            $notification->channels,
        );
        $symfonyNotification->content($notification->content);

        $this->notifier->send($symfonyNotification, ...$symfonyRecipients);
    }
}
