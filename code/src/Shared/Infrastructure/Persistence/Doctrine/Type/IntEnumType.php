<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class IntEnumType extends AbstractType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getSmallIntTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof \BackedEnum) {
            throw new \InvalidArgumentException('Value must be a backed enum');
        }

        $enumValue = $value->value;
        if (!is_int($enumValue)) {
            throw new \InvalidArgumentException('Enum value must be an integer');
        }

        return $enumValue;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Database value must be numeric');
        }

        return ($this->getIdClassName())::tryFrom(intval($value));
    }
}
