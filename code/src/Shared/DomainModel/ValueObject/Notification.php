<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class Notification
{
    public function __construct(
        public string $subject = '',
        public string $content = '',
        public array $channels = [],
    ) {
    }
}
