<?php

declare(strict_types=1);

namespace Services\DomainModel\Service;

interface ServicesInterface
{
    public function search(string $query): array;

    public function last(int $count): array;
}
