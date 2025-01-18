<?php

declare(strict_types=1);

namespace Services\Presentation\Api;

use OpenApi\Attributes as OA;
use Services\DomainModel\Service\ServicesInterface;
use Services\Presentation\Api\Request\ServicesSearchRequestDto;
use Services\Presentation\Api\Response\ServiceSearchJsonResponder;
use Shared\DomainModel\ValueObject\Currency;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ServiceSearchAction
{
    public function __construct(
        private string $defaultCurrency,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/services/search',
        summary: 'Search services with pagination',
        tags: ['Services']
    )]
    #[OA\Parameter(
        name: 'query',
        description: 'Search query string',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            maxLength: 255
        )
    )]
    #[OA\Parameter(
        name: 'currency',
        description: 'Currency',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            maxLength: 3
        )
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number (zero-based)',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 0
        )
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Number of items per page',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 1
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(type: 'object')
                ),
                new OA\Property(property: 'total', type: 'integer'),
                new OA\Property(property: 'page', type: 'integer'),
                new OA\Property(property: 'limit', type: 'integer'),
                new OA\Property(property: 'total_pages', type: 'integer'),
            ],
            type: 'object'
        )
    )]
    #[Route('/v1/services/search', name: 'api_services_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ServicesSearchRequestDto $request,
        ServicesInterface $service,
        ServiceSearchJsonResponder $responder,
    ): ServiceSearchJsonResponder {
        $currency = Currency::fromString($request->currency ?? $this->defaultCurrency);

        $data = $service->search(
            $request->query,
            $request->order(),
            $request->page,
            $request->limit,
        );

        $data = $data->jsonSerialize();
        $data['items'] = array_map(static function (array $item) use ($currency): array {
            $item['price'] = $item['price'].' '.$currency->symbol();

            return $item;
        }, $data['items']);

        return $responder->success($data, 'Ok')->respond();
    }
}
