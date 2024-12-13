<?php

declare(strict_types=1);

namespace App\Service\DomainModel\Service;

interface ServiceInterface
{
    public function search(string $query): array;
}
