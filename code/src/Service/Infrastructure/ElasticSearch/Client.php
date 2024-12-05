<?php

declare(strict_types=1);

namespace App\Service\Infrastructure\ElasticSearch;

use App\Service\DomainModel\Service\ServiceInterface;

final class Client implements ServiceInterface
{
    public function search(string $query): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Sample Service Title 1',
                'category' => 'Phone',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
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
            ],
            [
                'id' => 2,
                'category' => 'TV',
                'title' => 'Sample Service Title 2',
                'description' => 'This is a sample description for the second service offering.',
                'feedback_count' => '38',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '98% positive reviews',
                    'Online 2 hours ago',
                    'Response time: 1 hour'
                ],
                'rating' => 2,
                'review_count' => 345,
                'price' => '500'
            ],
            [
                'id' => 3,
                'category' => 'TV',
                'title' => 'Sample Service Title 2',
                'description' => 'This is a sample description for the second service offering.',
                'feedback_count' => '38',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '98% positive reviews',
                    'Online 2 hours ago',
                    'Response time: 1 hour'
                ],
                'rating' => 2,
                'review_count' => 345,
                'price' => '500'
            ],
            [
                'id' => 4,
                'category' => 'TV',
                'title' => 'Sample Service Title 2',
                'description' => 'This is a sample description for the second service offering.',
                'feedback_count' => '38',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '98% positive reviews',
                    'Online 2 hours ago',
                    'Response time: 1 hour'
                ],
                'rating' => 2,
                'review_count' => 345,
                'price' => '500'
            ],
        ];
    }
}