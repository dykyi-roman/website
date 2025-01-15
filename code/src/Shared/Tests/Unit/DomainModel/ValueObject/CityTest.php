<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\City;

#[CoversClass(City::class)]
final class CityTest extends TestCase
{
    public function testValidConstruction(): void
    {
        $city = new City('New York', 'nyc', '123 Broadway');

        self::assertSame('New York', $city->name);
        self::assertSame('nyc', $city->transcription);
        self::assertSame('123 Broadway', $city->address);
    }

    public function testValidConstructionWithoutAddress(): void
    {
        $city = new City('New York', 'nyc');

        self::assertSame('New York', $city->name);
        self::assertSame('nyc', $city->transcription);
        self::assertNull($city->address);
    }

    public function testJsonSerialization(): void
    {
        $city = new City('New York', 'nyc', '123 Broadway');
        $expected = [
            'name' => 'New York',
            'transcription' => 'nyc',
            'address' => '123 Broadway',
        ];

        self::assertSame($expected, $city->jsonSerialize());
    }

    /** @return array<string, array{string, string, string|null, string}> */
    public static function invalidCityDataProvider(): array
    {
        return [
            'empty name' => ['', 'nyc', null, 'City name cannot be empty'],
            'whitespace name' => ['  ', 'nyc', null, 'City name cannot be empty'],
            'empty transcription' => ['New York', '', null, 'City transcription cannot be empty'],
            'whitespace transcription' => ['New York', '  ', null, 'City transcription cannot be empty'],
            'empty address' => ['New York', 'nyc', '', 'If address is provided, it cannot be empty'],
            'whitespace address' => ['New York', 'nyc', '  ', 'If address is provided, it cannot be empty'],
        ];
    }

    #[DataProvider('invalidCityDataProvider')]
    public function testInvalidConstruction(string $name, string $transcription, ?string $address, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new City($name, $transcription, $address);
    }
}
