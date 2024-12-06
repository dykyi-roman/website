<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\ElasticSearch;

use App\Order\DomainModel\Service\OrderInterface;

final readonly class MockClient implements OrderInterface
{
    private const int COUNT = 10;

    public function search(string $query, int $page = 1, int $limit = 20): array
    {
        $items = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $items[] = [
                'id' => $i,
                'title' => 'Sample Order Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'category' => 'Phone',
                'url' => '/orders/item/' . $i,
                'feedback_count' => '42',
                'image_url' => 'https://scontent.xx.fbcdn.net/v/t39.30808-6/240452626_4417691464992012_8238282600430963777_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=f727a1&_nc_ohc=vXW6bc2sCH8Q7kNvgFLod1B&_nc_zt=23&_nc_ht=scontent.xx&_nc_gid=AcYcaCRfcsc7DbKeuVOTgWg&oh=00_AYAGh8YvfOWrtHN_3NyfVIKD4uN03R0RrJaGVejjYBNrNA&oe=67586285',
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
