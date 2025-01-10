<?php

declare(strict_types=1);

namespace Site\Location\DomainModel\Service;

use Shared\DomainModel\ValueObject\Location;

interface GeoLocationInterface
{
    public function locationByCoordinates(string $latitude, string $longitude): Location;
}