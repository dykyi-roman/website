<?php

declare(strict_types=1);

namespace Services\Presentation\Api;

use OpenApi\Attributes as OA;
use Services\DomainModel\Service\ServicesInterface;
use Services\Presentation\Api\Request\ServicesSearchRequestDto;
use Services\Presentation\Api\Response\ServiceSearchJsonResponder;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\Currency;
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

        /** @var PaginationDto<array{
         *     id: int,
         *     title: string,
         *     description: string,
         *     category: string,
         *     url: string,
         *     feedback_count: string,
         *     image_url: string,
         *     features: array<int, string>,
         *     rating: int,
         *     review_count: int,
         *     price: float
         * }> $data */
        $data = $service->search(
            $request->query,
            $request->order(),
            $request->page,
            $request->limit,
        );

        /** @var array{
         *     items: array<int, array{
         *         id: int,
         *         title: string,
         *         description: string,
         *         category: string,
         *         url: string,
         *         feedback_count: string,
         *         image_url: string,
         *         features: array<int, string>,
         *         rating: int,
         *         review_count: int,
         *         price: float
         *     }>,
         *     total: int,
         *     page: int,
         *     limit: int,
         *     total_pages: int
         * } */
        $result = $data->jsonSerialize();
        
        /** @var array{
         *     items: array<int, array{
         *         id: int,
         *         title: string,
         *         description: string,
         *         category: string,
         *         url: string,
         *         feedback_count: string,
         *         image_url: string,
         *         features: array<int, string>,
         *         rating: int,
         *         review_count: int,
         *         price: string
         *     }>,
         *     total: int,
         *     page: int,
         *     limit: int,
         *     total_pages: int
         * } $transformedResult */
        $transformedResult = [
            'items' => array_map(static function (array $item) use ($currency): array {
                return [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'category' => $item['category'],
                    'url' => $item['url'],
                    'feedback_count' => $item['feedback_count'],
                    'image_url' => $item['image_url'],
                    'features' => $item['features'],
                    'rating' => $item['rating'],
                    'review_count' => $item['review_count'],
                    'price' => $item['price'].' '.$currency->symbol(),
                ];
            }, $result['items']),
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'total_pages' => $result['total_pages'],
        ];

        return $responder->success($transformedResult, 'Ok')->respond();
    }
}
