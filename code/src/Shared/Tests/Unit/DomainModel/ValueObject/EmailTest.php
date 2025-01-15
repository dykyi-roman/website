<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Email;

#[CoversClass(Email::class)]
final class EmailTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $email = Email::fromString('test@example.com');
        self::assertSame('test@example.com', $email->value);
    }

    public function testToString(): void
    {
        $email = Email::fromString('test@example.com');
        self::assertSame('test@example.com', (string) $email);
    }

    public function testHash(): void
    {
        $email = Email::fromString('test@example.com');
        $expectedHash = hash('sha256', 'test@example.com');
        self::assertSame($expectedHash, $email->hash());
    }

    public function testEquals(): void
    {
        $email1 = Email::fromString('test@example.com');
        $email2 = Email::fromString('test@example.com');
        $email3 = Email::fromString('other@example.com');

        self::assertTrue($email1->equals($email2));
        self::assertTrue($email2->equals($email1));
        self::assertFalse($email1->equals($email3));
    }

    /** @return array<string, array{string}> */
    public static function invalidEmailDataProvider(): array
    {
        return [
            'empty string' => [''],
            'missing @' => ['testexample.com'],
            'missing domain' => ['test@'],
            'missing local part' => ['@example.com'],
            'invalid characters' => ['test@example@.com'],
            'multiple @ symbols' => ['test@test@example.com'],
            'spaces in email' => ['test @example.com'],
            'no domain extension' => ['test@example'],
        ];
    }

    #[DataProvider('invalidEmailDataProvider')]
    public function testInvalidConstruction(string $invalidEmail): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        Email::fromString($invalidEmail);
    }
}
