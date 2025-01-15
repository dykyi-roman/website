<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Currency;

#[CoversClass(Currency::class)]
final class CurrencyTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $currency = Currency::fromString('USD');
        self::assertSame('USD', $currency->code());
    }

    public function testEquals(): void
    {
        $usd1 = Currency::fromString('USD');
        $usd2 = Currency::fromString('USD');
        $eur = Currency::fromString('EUR');

        self::assertTrue($usd1->equals($usd2));
        self::assertTrue($usd2->equals($usd1));
        self::assertFalse($usd1->equals($eur));
    }

    public function testToString(): void
    {
        $currency = Currency::fromString('USD');
        self::assertSame('USD', (string) $currency);
    }

    /** @return array<string, array{string, string}> */
    public static function currencySymbolDataProvider(): array
    {
        return [
            'USD symbol' => ['USD', '$'],
            'EUR symbol' => ['EUR', '€'],
            'other currency uses code' => ['GBP', 'GBP'],
        ];
    }

    #[DataProvider('currencySymbolDataProvider')]
    public function testSymbol(string $code, string $expectedSymbol): void
    {
        $currency = Currency::fromString($code);
        self::assertSame($expectedSymbol, $currency->symbol());
    }

    /** @return array<string, array{string, string}> */
    public static function invalidCurrencyDataProvider(): array
    {
        return [
            'two characters' => ['US', 'Currency code must be exactly 3 characters long'],
            'four characters' => ['USDD', 'Currency code must be exactly 3 characters long'],
            'lowercase' => ['usd', 'Currency code must be in uppercase letters'],
            'mixed case' => ['UsD', 'Currency code must be in uppercase letters'],
        ];
    }

    #[DataProvider('invalidCurrencyDataProvider')]
    public function testInvalidConstruction(string $code, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Currency::fromString($code);
    }
}
