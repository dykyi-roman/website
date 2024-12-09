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
                'url' => '/services/item/' . $i,
                'feedback_count' => '42',
                'image_url' => 'https://scontent.xx.fbcdn.net/v/t39.30808-6/240122781_4417691381658687_1314923864255434509_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=f727a1&_nc_ohc=AizUbMWXODkQ7kNvgHOyNUW&_nc_zt=23&_nc_ht=scontent.xx&_nc_gid=AGO1PYGFi2Kud1mgLl1oJRW&oh=00_AYBpD2G0pjh2J3Cq_ef0bV4jsboOEHmzmwl3BrZkECdSow&oe=67585939',
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
