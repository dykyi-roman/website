<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\ElasticSearch;

use App\Partner\DomainModel\Service\ServiceInterface;

final class Client implements ServiceInterface
{
    public function all(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Sample Service Title 1',
                'description' => 'This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering. This is a sample description for the first service offering.',
                'feedback_count' => '42',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'category' => 'category_1',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '95% positive reviews',
                    'Online 4 hours ago',
                    'Response time: 2 hours'
                ],
                'price' => '500'
            ],
            [
                'id' => 2,
                'title' => 'Sample Service Title 2',
                'description' => 'This is a sample description for the second service offering.',
                'feedback_count' => '38',
                'image_url' => 'https://dykyi-roman.github.io/images/photo.jpg',
                'category' => 'category_1',
                'features' => [
                    'Super-premium',
                    'Master-freelancer',
                    '98% positive reviews',
                    'Online 2 hours ago',
                    'Response time: 1 hour'
                ],
                'price' => '500'
            ]
        ];
    }
}