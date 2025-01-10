<?php

declare(strict_types=1);

namespace Site\Location\Infrastructure\Clients;

use Shared\DomainModel\ValueObject\Location;
use Site\Location\DomainModel\Service\GeoLocationInterface;

final readonly class NominatimClient implements GeoLocationInterface
{
    public function __construct(
        private string $apiApiNominatimHost, // https://nominatim.openstreetmap.org/reverse
    ) {
    }

    public function locationByCoordinates(string $latitude, string $longitude): Location
    {

    }
}