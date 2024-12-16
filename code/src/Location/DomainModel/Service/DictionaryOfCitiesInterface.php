<?php

declare(strict_types=1);

namespace App\Location\DomainModel\Service;

use App\Location\DomainModel\Dto\CityDto;

interface DictionaryOfCitiesInterface
{
    /**
     * @return CityDto[]
     */
    public function cityByCountryAndLocale(string $countryCode, string $lang, string $city): array;
}
