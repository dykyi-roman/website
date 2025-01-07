<?php

declare(strict_types=1);

namespace Site\Dashboard\DomainModel\Dto;

final readonly class FeedItem
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $category,
        public string $url,
        public string $feedbackCount,
        public string $imageUrl,
        /** @var array<string> */
        public array $features,
        public int $rating,
        public int $reviewCount,
        public float $price,
    ) {
    }
}
