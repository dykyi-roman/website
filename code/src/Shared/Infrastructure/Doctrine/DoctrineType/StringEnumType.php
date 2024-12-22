<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Doctrine\DoctrineType;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class StringEnumType extends AbstractType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \BackedEnum) {
            throw new \InvalidArgumentException('Value must be a backed enum');
        }

        $enumValue = $value->value;
        if (!is_string($enumValue)) {
            throw new \InvalidArgumentException('Enum value must be a string');
        }

        return $enumValue;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Database value must be a scalar value');
        }

        return ($this->getIdClassName())::tryFrom((string) $value);
    }
}
