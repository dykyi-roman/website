<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

interface TokenGeneratorInterface
{
    public function generate(string $value, int $ttl = 3600): string;
}