<?php

declare(strict_types=1);

namespace Services\Infrastructure\ElasticSearch;

use Services\DomainModel\Enum\OrderType;
use Services\DomainModel\Service\ServicesInterface;

final readonly class MockClient implements ServicesInterface
{
    private const int COUNT = 25;

    public function search(
        string $query,
        OrderType $order,
        int $page,
        int $limit,
    ): array {
        $items = [];
        for ($i = 0; $i < self::COUNT; ++$i) {
            $items[] = [
                'id' => $i,
                'title' => 'Sample Service Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'category' => 'Phone',
                'url' => '/services/item/' . $i,
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
                'price' => '500',
            ];
        }

        // Calculate pagination
        $offset = ($page - 1) * $limit;
        $paginatedItems = array_slice($items, $offset, $limit);

        return [
            'items' => $paginatedItems,
            'total' => count($items),
            'page' => $page,
            'limit' => $limit,
            'total_pages' => (int)ceil(count($items) / $limit),
        ];
    }

    public function last(int $count): array
    {
        return [];
    }
}
