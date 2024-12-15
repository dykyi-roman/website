<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Api;

use App\Registration\DomainModel\Service\DictionaryOfCitiesInterface;
use App\Registration\Presentation\Api\Request\RegistrationCitiesRequest;
use App\Registration\Presentation\Api\Response\RegistrationCitiesResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v1/registration/cities/search', name: 'api_cities_by_country_search', methods: ['GET'])]
final readonly class RegistrationCitiesAction
{
    public function __construct(
        private DictionaryOfCitiesInterface $dictionaryOfCities,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/registration/cities/search',
        summary: 'Search cities by country and language',
        tags: ['Registration']
    )]
    #[OA\Parameter(
        name: 'countryCode',
        description: 'ISO 3166-1 alpha-2 country code',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            maxLength: 2,
            minLength: 2
        )
    )]
    #[OA\Parameter(
        name: 'lang',
        description: 'Language code (e.g., en, uk, es)',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            maxLength: 2,
            minLength: 2
        )
    )]
    #[OA\Parameter(
        name: 'city',
        description: 'City name to search for',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            maxLength: 255,
            minLength: 1
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'List of cities matching the search criteria',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'cities',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'countryCode', type: 'string', example: 'UA'),
                            new OA\Property(property: 'name', type: 'string', example: 'Київ'),
                            new OA\Property(property: 'transcription', type: 'string', example: 'Kyiv'),
                            new OA\Property(property: 'area', type: 'string', example: 'Київська область'),
                        ],
                        type: 'object'
                    )
                )
            ],
            type: 'object'
        )
    )]
    public function __invoke(
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
