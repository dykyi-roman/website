<?php

declare(strict_types=1);

namespace App\Service\DomainModel\Service;

interface ServicesInterface
{
    public function search(string $query): array;

    public function last(int $count): array;
}
