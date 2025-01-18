<?php

declare(strict_types=1);

namespace Orders\Presentation\Api;

use OpenApi\Attributes as OA;
use Orders\DomainModel\Service\OrdersInterface;
use Orders\Presentation\Api\Request\OrdersSearchRequestDto;
use Orders\Presentation\Api\Response\OrderSearchJsonResponder;
use Shared\DomainModel\ValueObject\Currency;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final readonly class OrderSearchAction
{
    public function __construct(
        private string $defaultCurrency,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/orders/search',
        summary: 'Search orders with pagination',
        tags: ['Orders']
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
    #[Route('/v1/orders/search', name: 'api_orders_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] OrdersSearchRequestDto $request,
        OrdersInterface $orders,
        OrderSearchJsonResponder $responder,
    ): OrderSearchJsonResponder {
        $currency = Currency::fromString($request->currency ?? $this->defaultCurrency);

        $data = $orders->search(
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
