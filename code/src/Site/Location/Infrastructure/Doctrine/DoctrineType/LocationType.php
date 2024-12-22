<?php

declare(strict_types=1);

namespace Site\Location\Infrastructure\Doctrine\DoctrineType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Shared\DomainModel\ValueObject\City;
use Shared\DomainModel\ValueObject\Country;
use Shared\DomainModel\ValueObject\Location;

final class LocationType extends Type
{
    public const string NAME = 'location';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * @throws \JsonException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Location
    {
        if (null === $value) {
            return null;
        }

        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON data structure');
        }

        if (!isset($data['country']) || !is_array($data['country']) || !isset($data['country']['code']) || !is_string($data['country']['code'])) {
            throw new \InvalidArgumentException('Invalid country data structure');
        }

        $country = new Country(
            code: $data['country']['code']
        );

        $city = null;
        if (isset($data['city']) && is_array($data['city'])) {
            if (!isset($data['city']['name']) || !is_string($data['city']['name'])) {
                throw new \InvalidArgumentException('Invalid city name');
            }
            if (!isset($data['city']['transcription']) || !is_string($data['city']['transcription'])) {
                throw new \InvalidArgumentException('Invalid city transcription');
            }
            
            $address = isset($data['city']['address']) ? (string)$data['city']['address'] : null;
            
            $city = new City(
                name: $data['city']['name'],
                transcription: $data['city']['transcription'],
                address: $address
            );
        }

        return new Location($country, $city);
    }

    /**
     * @throws \JsonException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
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
