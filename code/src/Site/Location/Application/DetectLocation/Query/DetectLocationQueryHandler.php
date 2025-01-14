<?php

declare(strict_types=1);

namespace Site\Location\Application\DetectLocation\Query;

use Shared\DomainModel\ValueObject\Location;
use Site\Location\DomainModel\Service\GeoLocationInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DetectLocationQueryHandler
{
    public function __construct(
        private GeoLocationInterface $geoLocation,
    ) {
    }

    public function __invoke(DetectLocationQuery $query): Location
    {
        return $this->geoLocation->locationByCoordinates(
            latitude: $query->latitude,
            longitude: $query->longitude,
        );
    }
}
