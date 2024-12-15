<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Api;

use App\Registration\DomainModel\Service\DictionaryOfCitiesInterface;
use App\Registration\Presentation\Api\Request\RegistrationCitiesRequest;
use App\Registration\Presentation\Api\Response\RegistrationCitiesResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v1/registration/cities', name: 'api_cities_by_country')]
final readonly class RegistrationCitiesAction
{
    public function __construct(
        private DictionaryOfCitiesInterface $dictionaryOfCities,
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(
        #[MapQueryString] RegistrationCitiesRequest $request
    ): RegistrationCitiesResponse {
        $cities = $this->dictionaryOfCities->cityByCountryAndLocale(
            $request->countryCode,
            $request->lang,
            $request->city,
        );

        return new RegistrationCitiesResponse($cities);
    }
}
