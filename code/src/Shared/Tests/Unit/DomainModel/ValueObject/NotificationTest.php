<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Notification;

#[CoversClass(Notification::class)]
final class NotificationTest extends TestCase
{
    public function testConstructionWithAllValues(): void
    {
        $notification = new Notification(
            'Test Subject',
            'Test Content',
            ['email', 'sms']
        );

        self::assertSame('Test Subject', $notification->subject);
        self::assertSame('Test Content', $notification->content);
        self::assertSame(['email', 'sms'], $notification->channels);
    }

    public function testConstructionWithDefaultValues(): void
    {
        $notification = new Notification();

        self::assertSame('', $notification->subject);
        self::assertSame('', $notification->content);
        self::assertSame([], $notification->channels);
    }

    public function testConstructionWithOnlySubject(): void
    {
        $notification = new Notification('Test Subject');

        self::assertSame('Test Subject', $notification->subject);
        self::assertSame('', $notification->content);
        self::assertSame([], $notification->channels);
    }

    public function testConstructionWithSubjectAndContent(): void
    {
        $notification = new Notification('Test Subject', 'Test Content');

        self::assertSame('Test Subject', $notification->subject);
        self::assertSame('Test Content', $notification->content);
        self::assertSame([], $notification->channels);
    }
}
