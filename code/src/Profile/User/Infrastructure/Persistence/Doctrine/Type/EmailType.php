<?php

declare(strict_types=1);

namespace Profile\User\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Shared\DomainModel\ValueObject\Email;

final class EmailType extends Type
{
    public const string NAME = 'email';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Email value must be a scalar value');
        }

        return Email::fromString(strval($value));
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (!$value instanceof Email) {
            throw new \InvalidArgumentException('Value must be an instance of Email');
        }

        return $value->__toString();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
