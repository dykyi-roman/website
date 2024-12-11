<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Doctrine\DoctrineType;

use App\Client\DomainModel\Enum\ClientStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

final class ClientStatusType extends Type
{
    public function getName(): string
    {
        return 'client_status';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getSmallIntTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        if ($value === null) {
            return ClientStatus::ACTIVE->value;
        }

        if (!$value instanceof ClientStatus) {
            throw new InvalidArgumentException(
                sprintf('Value must be an instance of %s', ClientStatus::class)
            );
        }

        return $value->value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ClientStatus
    {
        if ($value === null) {
            return null;
        }

        return ClientStatus::tryFrom((int)$value);
    }
}
