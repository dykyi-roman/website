<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Location;

#[CoversClass(Location::class)]
final class LocationTest extends TestCase
{
    public function testConstructionWithBothValues(): void
    {
        $country = new Country('US');
        $city = new City('New York', 'nyc', '123 Broadway');
        $location = new Location($country, $city);

        self::assertSame($country, $location->country);
        self::assertSame($city, $location->city);
    }

    public function testConstructionWithOnlyCountry(): void
    {
        $country = new Country('US');
        $location = new Location($country);

        self::assertSame($country, $location->country);
        self::assertNull($location->city);
    }

    public function testConstructionWithOnlyCity(): void
    {
        $city = new City('New York', 'nyc', '123 Broadway');
        $location = new Location(null, $city);

        self::assertNull($location->country);
        self::assertSame($city, $location->city);
    }

    public function testConstructionWithNoValues(): void
    {
        $location = new Location();

        self::assertNull($location->country);
        self::assertNull($location->city);
    }

    public function testJsonSerializationWithBothValues(): void
    {
        $country = new Country('US');
        $city = new City('New York', 'nyc', '123 Broadway');
        $location = new Location($country, $city);

        $expected = [
            'country' => ['code' => 'US'],
            'city' => [
                'name' => 'New York',
                'transcription' => 'nyc',
                'address' => '123 Broadway',
            ],
        ];

        self::assertSame($expected, $location->jsonSerialize());
    }

    public function testJsonSerializationWithOnlyCountry(): void
    {
        $country = new Country('US');
        $location = new Location($country);

        $expected = [
            'country' => ['code' => 'US'],
            'city' => null,
        ];

        self::assertSame($expected, $location->jsonSerialize());
    }

    public function testJsonSerializationWithOnlyCity(): void
    {
        $city = new City('New York', 'nyc', '123 Broadway');
        $location = new Location(null, $city);

        $expected = [
            'country' => null,
            'city' => [
                'name' => 'New York',
                'transcription' => 'nyc',
                'address' => '123 Broadway',
            ],
        ];

        self::assertSame($expected, $location->jsonSerialize());
    }

    public function testJsonSerializationWithNoValues(): void
    {
        $location = new Location();

        $expected = [
            'country' => null,
            'city' => null,
        ];

        self::assertSame($expected, $location->jsonSerialize());
    }
}
