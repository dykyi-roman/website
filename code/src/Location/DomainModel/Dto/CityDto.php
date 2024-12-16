<?php

declare(strict_types=1);

namespace App\Location\DomainModel\Dto;

final readonly class CityDto
{
    public function __construct(
        public string $countryCode,
        public string $name,
        public string $transcription,
        public string $area,
    ) {
    }
}
