<?php

declare(strict_types=1);

namespace App\Service\Infrastructure\ElasticSearch;

use App\Service\DomainModel\Service\ServiceInterface;

final readonly class Client implements ServiceInterface
{
    public function __construct(
        private string $appHost,
    ) {
    }

    public function search(string $query): array
    {
        $items = [];
        for ($i = 1; $i < 25; $i++) {
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

        return $items;
    }
}