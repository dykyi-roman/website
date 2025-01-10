<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api;

use OpenApi\Attributes as OA;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Location;
use Site\Location\Application\DetectLocation\Query\DetectLocationQuery;
use Site\Location\Presentation\Api\Request\DetectLocationRequest;
use Site\Location\Presentation\Api\Response\DetectLocationResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/location/detect', name: 'api_location_detect', methods: ['GET'])]
final readonly class DetectLocationAction
{
    #[OA\Get(
        path: '/api/v1/location/detect',
        summary: 'Detect location by coordinates',
        tags: ['Location']
    )]
    #[OA\Parameter(
        name: 'latitude',
        description: 'Latitude coordinate',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            pattern: '^[-]?((([0-8]?[0-9])(\.[0-9]+)?)|90(\.0+)?)$'
        )
    )]
    #[OA\Parameter(
        name: 'longitude',
        description: 'Longitude coordinate',
        in: 'query',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            pattern: '^[-]?((([0-9]?[0-9]|1[0-7][0-9])(\.[0-9]+)?)|180(\.0+)?)$'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Location information',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'country', type: 'string', example: 'UA'),
                new OA\Property(property: 'city', type: 'string', example: 'Київ'),
            ],
            type: 'object'
        )
    )]
    public function __invoke(
        #[MapQueryString] DetectLocationRequest $request,
        MessageBusInterface $messageBus,
    ): DetectLocationResponse {
        /** @var Location $location */
        $location = $messageBus->dispatch(
            new DetectLocationQuery(
                latitude: $request->latitude,
                longitude: $request->longitude,
            )
        );

        return new DetectLocationResponse($location);
    }
}
