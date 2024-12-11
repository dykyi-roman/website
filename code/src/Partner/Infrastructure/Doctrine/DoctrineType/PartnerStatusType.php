<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\Doctrine\DoctrineType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use App\Partner\DomainModel\Enum\PartnerStatus;
use InvalidArgumentException;

final class PartnerStatusType extends Type
{
    public function getName(): string
    {
        return 'partner_status';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getSmallIntTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        if ($value === null) {
            return PartnerStatus::ACTIVE->value;
        }

        if (!$value instanceof PartnerStatus) {
            throw new InvalidArgumentException(
                sprintf('Value must be an instance of %s', PartnerStatus::class)
            );
        }

        return $value->value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PartnerStatus
    {
        if ($value === null) {
            return null;
        }

        return PartnerStatus::tryFrom((int)$value);
    }
}