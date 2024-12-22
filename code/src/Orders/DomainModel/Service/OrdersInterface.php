<?php

declare(strict_types=1);

namespace Orders\DomainModel\Service;

interface OrdersInterface
{
    public function search(string $query): array;

    public function last(int $count): array;
}
