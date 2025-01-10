<?php

declare(strict_types=1);

namespace Site\Location\DomainModel\Dto;

final readonly class CityDto
{
    public function __construct(
        public string $name,
        public string $transcription,
        public string $area,
    ) {
    }
}
