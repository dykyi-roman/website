<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

interface DictionaryOfCitiesInterface
{
    public function cityByCountryAndLocale(string $countryCode, string $lang, string $city): array;
}
