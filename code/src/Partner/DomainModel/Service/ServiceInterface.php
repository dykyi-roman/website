<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Service;

interface ServiceInterface
{
    public function all(): array;
}