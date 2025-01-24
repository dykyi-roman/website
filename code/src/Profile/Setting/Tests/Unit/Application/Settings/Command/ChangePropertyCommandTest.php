<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\Settings\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\Settings\Command\ChangePropertyCommand;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\ValueObject\Property;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(ChangePropertyCommand::class)]
final class ChangePropertyCommandTest extends TestCase
{
    public function testCreateCommandWithSingleProperty(): void
    {
        $userId = new UserId();
        $property = new Property(
            category: PropertyCategory::GENERAL,
            name: PropertyName::SETTINGS_GENERAL_LANGUAGE,
            value: 'en'
        );

        $command = new ChangePropertyCommand(
            id: $userId,
            properties: [$property]
        );

        self::assertSame($userId, $command->id);
        self::assertCount(1, $command->properties);
        self::assertSame($property, $command->properties[0]);
        self::assertSame(PropertyCategory::GENERAL, $command->properties[0]->category);
        self::assertSame(PropertyName::SETTINGS_GENERAL_LANGUAGE, $command->properties[0]->name);
        self::assertSame('en', $command->properties[0]->value);
    }

    public function testCreateCommandWithMultipleProperties(): void
    {
        $userId = new UserId();
        $properties = [
            new Property(
                category: PropertyCategory::GENERAL,
                name: PropertyName::SETTINGS_GENERAL_LANGUAGE,
                value: 'en'
            ),
            new Property(
                category: PropertyCategory::GENERAL,
                name: PropertyName::SETTINGS_GENERAL_CURRENCY,
                value: 'USD'
            ),
            new Property(
                category: PropertyCategory::GENERAL,
                name: PropertyName::SETTINGS_GENERAL_THEME,
                value: 'dark'
            ),
        ];

        $command = new ChangePropertyCommand(
            id: $userId,
            properties: $properties
        );

        self::assertSame($userId, $command->id);
        self::assertCount(3, $command->properties);

        // Verify first property
        self::assertSame($properties[0], $command->properties[0]);
        self::assertSame(PropertyCategory::GENERAL, $command->properties[0]->category);
        self::assertSame(PropertyName::SETTINGS_GENERAL_LANGUAGE, $command->properties[0]->name);
        self::assertSame('en', $command->properties[0]->value);

        // Verify second property
        self::assertSame($properties[1], $command->properties[1]);
        self::assertSame(PropertyCategory::GENERAL, $command->properties[1]->category);
        self::assertSame(PropertyName::SETTINGS_GENERAL_CURRENCY, $command->properties[1]->name);
        self::assertSame('USD', $command->properties[1]->value);

        // Verify third property
        self::assertSame($properties[2], $command->properties[2]);
        self::assertSame(PropertyCategory::GENERAL, $command->properties[2]->category);
        self::assertSame(PropertyName::SETTINGS_GENERAL_THEME, $command->properties[2]->name);
        self::assertSame('dark', $command->properties[2]->value);
    }
}
