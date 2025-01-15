<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Currency;
use Shared\DomainModel\ValueObject\Money;

#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    public function testCreateWithValidAmount(): void
    {
        $currency = Currency::fromString('USD');
        $money = Money::create(100.50, $currency);

        self::assertSame(100.50, $money->getAmount());
        self::assertTrue($currency->equals($money->getCurrency()));
    }

    public function testFromPrimitivesWithValidAmount(): void
    {
        $money = Money::fromPrimitives(100.50, 'USD');

        self::assertSame(100.50, $money->getAmount());
        self::assertTrue(Currency::fromString('USD')->equals($money->getCurrency()));
    }

    public function testAddWithSameCurrency(): void
    {
        $money1 = Money::fromPrimitives(100.50, 'USD');
        $money2 = Money::fromPrimitives(50.25, 'USD');

        $result = $money1->add($money2);

        self::assertSame(150.75, $result->getAmount());
        self::assertTrue(Currency::fromString('USD')->equals($result->getCurrency()));
    }

    public function testMultiplyByPositiveNumber(): void
    {
        $money = Money::fromPrimitives(100.50, 'USD');
        $result = $money->multiply(2.5);

        self::assertSame(251.25, $result->getAmount());
        self::assertTrue(Currency::fromString('USD')->equals($result->getCurrency()));
    }

    public function testEquals(): void
    {
        $money1 = Money::fromPrimitives(100.50, 'USD');
        $money2 = Money::fromPrimitives(100.50, 'USD');
        $money3 = Money::fromPrimitives(100.50, 'EUR');
        $money4 = Money::fromPrimitives(200.00, 'USD');

        self::assertTrue($money1->equals($money2));
        self::assertTrue($money2->equals($money1));
        self::assertFalse($money1->equals($money3));
        self::assertFalse($money1->equals($money4));
    }

    public function testCreateWithNegativeAmountThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        Money::fromPrimitives(-100.50, 'USD');
    }

    public function testAddWithDifferentCurrenciesThrowsException(): void
    {
        $money1 = Money::fromPrimitives(100.50, 'USD');
        $money2 = Money::fromPrimitives(50.25, 'EUR');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add money with different currencies');

        $money1->add($money2);
    }

    public function testMultiplyWithNegativeMultiplierThrowsException(): void
    {
        $money = Money::fromPrimitives(100.50, 'USD');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplier cannot be negative');

        $money->multiply(-2);
    }

    public function testMultiplyByZero(): void
    {
        $money = Money::fromPrimitives(100.50, 'USD');
        $result = $money->multiply(0);

        self::assertSame(0.0, $result->getAmount());
        self::assertTrue(Currency::fromString('USD')->equals($result->getCurrency()));
    }
}
