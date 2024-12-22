<?php

declare(strict_types=1);

namespace Site\Location\DomainModel\Service;

use Site\Location\DomainModel\Dto\CityDto;

interface DictionaryOfCitiesInterface
{
    /**
     * @return CityDto[]
     */
    public function cityByCountryAndLocale(string $countryCode, string $lang, string $city): array;
}
