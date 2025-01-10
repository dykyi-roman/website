<?php

declare(strict_types=1);

namespace Site\Location\Application\Query;

use Site\Location\DomainModel\Dto\CityDto;
use Site\Location\DomainModel\Service\DictionaryOfCitiesInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCitiesDictionaryQueryHandler
{
    public function __construct(
        private DictionaryOfCitiesInterface $dictionaryOfCities,
    ) {
    }

    /**
     * @return array<CityDto>
     */
    public function __invoke(GetCitiesDictionaryQuery $query): array
    {
        $citiesDto = $this->dictionaryOfCities->cityByCountryAndLocale(
            $query->countryCode,
            $query->lang,
            $query->city,
        );

        $cities = [];
        foreach ($citiesDto as $cityDto) {
            $cities[] = new CityDto(
                name: $cityDto->name,
                transcription: $cityDto->transcription,
                area: $cityDto->area,
            );
        }

        return $cities;
    }
}
