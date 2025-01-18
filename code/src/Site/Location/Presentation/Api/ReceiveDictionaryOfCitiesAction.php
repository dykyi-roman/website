<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api;

use OpenApi\Attributes as OA;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\Location\Application\CitiesDictionary\Query\GetCitiesDictionaryQuery;
use Site\Location\DomainModel\Dto\CityDto;
use Site\Location\Presentation\Api\Request\DictionaryOfCitiesRequest;
use Site\Location\Presentation\Api\Response\DictionaryOfCitiesJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/location/cities', name: 'api_cities_by_country_search', methods: ['GET'])]
final readonly class ReceiveDictionaryOfCitiesAction
{
    #[OA\Get(
        path: '/api/v1/location/cities',
        summary: 'Search cities by country and language',
        tags: ['Location']
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
                ),
            ],
            type: 'object'
        )
    )]
    public function __invoke(
        #[MapQueryString] DictionaryOfCitiesRequest $request,
        MessageBusInterface $messageBus,
    ): DictionaryOfCitiesJsonResponder {
        /** @var array<CityDto> $cities */
        $cities = $messageBus->dispatch(
            new GetCitiesDictionaryQuery(
                countryCode: $request->countryCode,
                lang: $request->lang,
                city: $request->city,
            )
        );

        return new DictionaryOfCitiesJsonResponder($cities);
    }
}
