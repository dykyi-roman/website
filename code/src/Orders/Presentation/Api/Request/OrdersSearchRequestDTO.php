<?php

declare(strict_types=1);

namespace Orders\Presentation\Api\Request;

use Orders\DomainModel\Enum\OrderType;
use Symfony\Component\Validator\Constraints as Assert;

final class OrdersSearchRequestDTO
{
    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public string $query = '';

    #[Assert\Type('string')]
    #[Assert\Length(max: 255)]
    public ?string $currency = null;

    #[Assert\Type('string')]
    #[Assert\Length(max: 32)]
    private string $order = OrderType::DATE_DESC->value;

    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public int $page = 1;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1, max: 100)]
    public int $limit = 10;

    public function order(): OrderType
    {
        return OrderType::from($this->order);
    }
}
