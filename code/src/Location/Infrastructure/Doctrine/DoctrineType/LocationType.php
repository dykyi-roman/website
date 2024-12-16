<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Doctrine\DoctrineType;

use App\Shared\DomainModel\ValueObject\City;
use App\Shared\DomainModel\ValueObject\Country;
use App\Shared\DomainModel\ValueObject\Location;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use JsonException;

final class LocationType extends Type
{
    public const string NAME = 'location';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * @throws JsonException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Location
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        $country = new Country(
            code: $data['country']['code']
        );

        $city = null;
        if (isset($data['city'])) {
            $city = new City(
                name: $data['city']['name'],
                transcription: $data['city']['transcription'],
                address: $data['city']['address'] ?? null
            );
        }

        return new Location($country, $city);
    }

    /**
     * @throws JsonException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Location) {
            throw new \InvalidArgumentException('Value must be an instance of Location');
        }

        return json_encode($value->jsonSerialize(), JSON_THROW_ON_ERROR);
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
