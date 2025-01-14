<?php

declare(strict_types=1);

namespace Site\Location\Application\DetectLocation\Query;

/**
 * @see DetectLocationQueryHandler
 */
final readonly class DetectLocationQuery
{
    public function __construct(
        public string $latitude,
        public string $longitude,
    ) {
    }
}
