<?php

declare(strict_types=1);

namespace App\Shared\DomainModel\ValueObject;

final readonly class Notification
{
    public function __construct(
        public string $subject = '',
        public array $channels = []
    ) {
    }
}