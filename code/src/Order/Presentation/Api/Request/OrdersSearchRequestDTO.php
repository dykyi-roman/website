<?php

declare(strict_types=1);

namespace App\Order\Presentation\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class OrdersSearchRequestDTO
{
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public string $query = '';

    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $page = 1;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 10;
}
