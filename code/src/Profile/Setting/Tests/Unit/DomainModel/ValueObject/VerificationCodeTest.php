<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;

#[CoversClass(VerificationCode::class)]
final class VerificationCodeTest extends TestCase
{
    public function testGenerate(): void
    {
        $code = VerificationCode::generate();

        self::assertMatchesRegularExpression('/^\d{6}$/', $code->toString());
    }

    public function testFromString(): void
    {
        $code = VerificationCode::fromString('123456');

        self::assertSame('123456', $code->toString());
    }

    public function testFromStringWithInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Verification code must be 6 digits');

        VerificationCode::fromString('12345');
    }

    public function testFromStringWithNonDigits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Verification code must be 6 digits');

        VerificationCode::fromString('12345a');
    }

    public function testEquals(): void
    {
        $code1 = VerificationCode::fromString('123456');
        $code2 = VerificationCode::fromString('123456');
        $code3 = VerificationCode::fromString('654321');

        self::assertTrue($code1->equals($code2));
        self::assertFalse($code1->equals($code3));
    }

    public function testToString(): void
    {
        $code = VerificationCode::fromString('123456');

        self::assertSame('123456', $code->toString());
        self::assertSame('123456', (string) $code);
    }
}
