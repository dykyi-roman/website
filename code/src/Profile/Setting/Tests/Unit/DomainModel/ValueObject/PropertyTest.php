<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\ValueObject;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\ValueObject\Property;

#[CoversClass(Property::class)]
final class PropertyTest extends TestCase
{
    public function testConstructorWithScalarValue(): void
    {
        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            'en'
        );

        self::assertSame('en', $property->value());
    }

    public function testConstructorWithBooleanValue(): void
    {
        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            true
        );

        self::assertSame('1', $property->value());

        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            false
        );

        self::assertSame('0', $property->value());
    }

    public function testConstructorWithDateTimeValue(): void
    {
        $date = new \DateTime('2023-01-01 12:00:00');
        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            $date
        );

        self::assertSame('2023-01-01 12:00:00', $property->value());
    }

    public function testConstructorWithNullValue(): void
    {
        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            null
        );

        self::assertSame('', $property->value());
    }

    public function testConstructorWithObjectImplementingToString(): void
    {
        $object = new class implements \Stringable {
            public function __toString(): string
            {
                return 'test_string';
            }
        };

        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            $object
        );

        self::assertSame('test_string', $property->value());
    }

    public function testConstructorWithInvalidValue(): void
    {
        $object = new class {};  // Anonymous class without __toString

        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            $object
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert value to string');

        $property->value();
    }

    public function testJsonSerialization(): void
    {
        $property = new Property(
            PropertyCategory::NOTIFICATION,
            PropertyName::SETTINGS_GENERAL_LANGUAGE,
            'en'
        );

        $expected = [
            'category' => PropertyCategory::NOTIFICATION->value,
            'name' => PropertyName::SETTINGS_GENERAL_LANGUAGE->value,
            'value' => 'en',
        ];

        self::assertSame($expected, $property->jsonSerialize());
    }
}
