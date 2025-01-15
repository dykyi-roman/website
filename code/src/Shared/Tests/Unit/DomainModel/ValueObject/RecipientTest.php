<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\EmailRecipientInterface;
use Shared\DomainModel\ValueObject\Recipient;
use Shared\DomainModel\ValueObject\SmsRecipientInterface;

#[CoversClass(Recipient::class)]
final class RecipientTest extends TestCase
{
    public function testConstructionWithBothValues(): void
    {
        $recipient = new Recipient('test@example.com', '+1234567890');

        self::assertSame('test@example.com', $recipient->getEmail());
        self::assertSame('+1234567890', $recipient->getPhone());
    }

    public function testConstructionWithOnlyEmail(): void
    {
        $recipient = new Recipient('test@example.com');

        self::assertSame('test@example.com', $recipient->getEmail());
        self::assertSame('', $recipient->getPhone());
    }

    public function testConstructionWithOnlyPhone(): void
    {
        $recipient = new Recipient('', '+1234567890');

        self::assertSame('', $recipient->getEmail());
        self::assertSame('+1234567890', $recipient->getPhone());
    }

    public function testConstructionWithBothEmptyValuesThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('"%s" needs an email or a phone but both cannot be empty.', Recipient::class));

        new Recipient('', '');
    }

    public function testImplementsEmailRecipientInterface(): void
    {
        $recipient = new Recipient('test@example.com');

        self::assertInstanceOf(EmailRecipientInterface::class, $recipient);
        self::assertSame('test@example.com', $recipient->getEmail());
    }

    public function testImplementsSmsRecipientInterface(): void
    {
        $recipient = new Recipient(phone: '+1234567890');

        self::assertInstanceOf(SmsRecipientInterface::class, $recipient);
        self::assertSame('+1234567890', $recipient->getPhone());
    }
}
