<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use App\Registration\DomainModel\Dto\CityDto;

interface DictionaryOfCitiesInterface
{
    /**
     * @return CityDto[]
     */
    public function cityByCountryAndLocale(string $countryCode, string $lang, string $city): array;
}
