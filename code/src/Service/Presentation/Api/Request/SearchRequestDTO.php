<?php

declare(strict_types=1);

namespace App\Service\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

final readonly class SearchRequestDTO
{
    public function __construct(
        #[MapQueryParameter('query')]
        #[Assert\Type('string')]
        #[Assert\Length(max: 255)]
        public ?string $query = '',

        #[MapQueryParameter('page')]
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public ?int $page = 1,

        #[MapQueryParameter('limit')]
        #[Assert\Type('integer')]
        #[Assert\Range(min: 1, max: 100)]
        public ?int $limit = 20,
    ) {
    }
}
