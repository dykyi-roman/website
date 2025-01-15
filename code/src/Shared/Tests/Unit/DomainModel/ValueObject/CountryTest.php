<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Country;

#[CoversClass(Country::class)]
final class CountryTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $country = new Country('US');
        self::assertSame('US', $country->code);
    }

    public function testJsonSerialization(): void
    {
        $country = new Country('GB');
        $expected = ['code' => 'GB'];

        self::assertSame($expected, $country->jsonSerialize());
    }

    /** @return array<string, array{string, string}> */
    public static function invalidCountryDataProvider(): array
    {
        return [
            'empty code' => ['', 'Country code cannot be empty'],
            'whitespace code' => ['  ', 'Country code cannot be empty'],
            'single character' => ['A', 'Country code must be exactly 2 characters long (ISO 3166-1 alpha-2)'],
            'three characters' => ['USA', 'Country code must be exactly 2 characters long (ISO 3166-1 alpha-2)'],
        ];
    }

    #[DataProvider('invalidCountryDataProvider')]
    public function testInvalidConstruction(string $code, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new Country($code);
    }
}
