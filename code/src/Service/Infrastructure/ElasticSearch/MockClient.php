<?php

declare(strict_types=1);

namespace App\Service\Infrastructure\ElasticSearch;

use App\Service\DomainModel\Service\ServiceInterface;

final readonly class MockClient implements ServiceInterface
{
    private const int COUNT = 25;

    public function search(string $query, int $page = 1, int $limit = 20): array
    {
        $items = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $items[] = [
                'id' => $i,
                'title' => 'Sample Service Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'category' => 'Phone',
                'url' => '/service/' . $i,
                'feedback_count' => '42',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '95% positive reviews',
                    'Online 4 hours ago',
                    'Response time: 2 hours'
                ],
                'rating' => 4,
                'review_count' => 34,
                'price' => '500'
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
            'total_pages' => ceil(count($items) / $limit)
        ];
    }
}
