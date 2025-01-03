<?php

declare(strict_types=1);

namespace Orders\Presentation\Api;

use OpenApi\Attributes as OA;
use Orders\DomainModel\Service\OrdersInterface;
use Orders\Presentation\Api\Request\OrdersSearchRequestDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

final class OrderSearchAction
{
    #[OA\Get(
        path: '/api/v1/orders/search',
        summary: 'Search orders with pagination',
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
    #[Route('/v1/orders/search', name: 'api_orders_search', methods: ['GET'])]
    public function __invoke(
        Request $request,
        #[MapQueryString] OrdersSearchRequestDTO $searchRequest,
        OrdersInterface $orders,
    ): JsonResponse {
        $result = $orders->search(
            $searchRequest->query,
            $searchRequest->order(),
            $searchRequest->page,
            $searchRequest->limit,
        );

        return new JsonResponse($result);
    }
}
