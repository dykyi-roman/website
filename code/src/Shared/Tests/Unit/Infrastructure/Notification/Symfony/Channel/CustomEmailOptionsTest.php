<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\Notification\Symfony\Channel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\Notification\Symfony\Channel\CustomEmailOptions;

#[CoversClass(CustomEmailOptions::class)]
final class CustomEmailOptionsTest extends TestCase
{
    public function testConstructorWithoutRecipientId(): void
    {
        $options = new CustomEmailOptions();
        self::assertEmpty($options->getRecipientId());
    }

    public function testConstructorWithRecipientId(): void
    {
        $options = new CustomEmailOptions('test@example.com');
        self::assertSame('test@example.com', $options->getRecipientId());
    }

    public function testRecipientIdSetter(): void
    {
        $options = new CustomEmailOptions();
        $options->recipientId('test@example.com');
        self::assertSame('test@example.com', $options->getRecipientId());
    }

    public function testToArray(): void
    {
        $options = new CustomEmailOptions('test@example.com');
        $array = $options->toArray();

        self::assertArrayHasKey('recipientId', $array);
        self::assertSame('test@example.com', $array['recipientId']);
    }

    public function testGetTransport(): void
    {
        $options = new CustomEmailOptions();
        self::assertSame('custom-email', $options->getTransport());
    }
}
