<?php

declare(strict_types=1);

namespace Orders\Infrastructure\ElasticSearch;

use Orders\DomainModel\Enum\OrderType;
use Orders\DomainModel\Service\OrdersInterface;
use Shared\DomainModel\Dto\PaginationDto;

final readonly class MockClient implements OrdersInterface
{
    private const int COUNT = 10;

    /**
     * @return array{
     *     items: array<int, array{
     *         id: int,
     *         title: string,
     *         description: string,
     *         category: string,
     *         url: string,
     *         feedback_count: string,
     *         image_url: string,
     *         features: array<string>,
     *         rating: int,
     *         review_count: int,
     *         price: float
     *     }>,
     *     total: int,
     *     page: int,
     *     limit: int,
     *     total_pages: int
     * }
     */
    public function search(
        string $query,
        OrderType $order,
        int $page,
        int $limit,
    ): PaginationDto {
        $items = [];
        for ($i = 0; $i < self::COUNT; ++$i) {
            $items[] = [
                'id' => $i,
                'title' => 'Sample Order Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'category' => 'Phone',
                'url' => '/orders/item/'.$i,
                'feedback_count' => '42',
                'image_url' => '',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '95% positive reviews',
                    'Online 4 hours ago',
                    'Response time: 2 hours',
                ],
                'rating' => 4,
                'review_count' => 34,
                'price' => 500.00,
            ];
        }

        return new PaginationDto($items, $page, $limit);
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     title: string,
     *     description: string,
     *     category: string,
     *     url: string,
     *     feedback_count: string,
     *     image_url: string,
     *     features: array<string>,
     *     rating: int,
     *     review_count: int,
     *     price: float
     * }>
     */
    public function last(int $count): array
    {
        return [];
    }
}
