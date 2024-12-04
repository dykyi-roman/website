<?php

declare(strict_types=1);

namespace App\Service\Infrastructure\ElasticSearch;

use App\Service\DomainModel\Service\ServiceInterface;

final class Client implements ServiceInterface
{
    public function all(): array
    {
        return [
            [
                'title' => 'Sample Service Title 1',
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
                'price' => '500'
            ],
            [
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
                'price' => '500'
            ]
        ];
    }
}