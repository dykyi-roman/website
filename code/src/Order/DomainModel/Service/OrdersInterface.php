<?php

declare(strict_types=1);

namespace App\Order\DomainModel\Service;

interface OrdersInterface
{
    public function search(string $query): array;

    public function last(int $count): array;
}
