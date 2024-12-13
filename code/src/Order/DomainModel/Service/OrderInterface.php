<?php

declare(strict_types=1);

namespace App\Order\DomainModel\Service;

interface OrderInterface
{
    public function search(string $query): array;
}
