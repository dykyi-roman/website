<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\Notification;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\RecipientInterface;
use Shared\Infrastructure\Notification\NotificationAdapter;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

#[CoversClass(NotificationAdapter::class)]
final class NotificationAdapterTest extends TestCase
{
    public function testSendNotification(): void
    {
        $notifier = $this->createMock(NotifierInterface::class);
        $adapter = new NotificationAdapter($notifier);

        $recipient = $this->createMock(RecipientInterface::class);
        $recipient->method('getEmail')->willReturn('test@example.com');
        $recipient->method('getPhone')->willReturn('+1234567890');

        $notification = new Notification(
            'Test Subject',
            'Test Content',
            ['email']
        );

        $notifier->expects(self::once())
            ->method('send')
            ->with(
                self::callback(function ($symfonyNotification) {
                    return 'Test Subject' === $symfonyNotification->getSubject()
                        && 'Test Content' === $symfonyNotification->getContent();
                }),
                self::callback(function ($recipient) {
                    return $recipient instanceof Recipient
                        && 'test@example.com' === $recipient->getEmail()
                        && '+1234567890' === $recipient->getPhone();
                })
            );

        $adapter->send($notification, $recipient);
    }
}
