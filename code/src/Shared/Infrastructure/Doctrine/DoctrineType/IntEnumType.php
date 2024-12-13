<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DoctrineType;

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

        $this->ensureEnum($value);

        return $value->value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        return ($this->getIdClassName())::tryFrom((int) $value);
    }
}
