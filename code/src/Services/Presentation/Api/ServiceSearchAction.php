<?php

declare(strict_types=1);

namespace Services\Presentation\Api;

use OpenApi\Attributes as OA;
use Services\DomainModel\Service\ServicesInterface;
use Services\Presentation\Api\Request\ServicesSearchRequestDTO;
use Shared\DomainModel\ValueObject\Currency;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

final readonly class ServiceSearchAction
{
    public function __construct(
        private string $defaultCurrency,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/services/search',
        summary: 'Search services with pagination',
        tags: ['Service']
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
        Request $request,
        #[MapQueryString] ServicesSearchRequestDTO $searchRequest,
        ServicesInterface $service,
    ): JsonResponse {
        $currency = Currency::fromString($searchRequest->currency ?? $this->defaultCurrency);

        $data = $service->search(
            $searchRequest->query,
            $searchRequest->order(),
            $searchRequest->page,
            $searchRequest->limit,
        );

        $data['items'] = array_map(static function (array $item) use ($currency): array {
            $item['price'] = $item['price'].' '.$currency->symbol();

            return $item;
        }, $data['items']);

        return new JsonResponse($data);
    }
}
