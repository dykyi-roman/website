<?php

declare(strict_types=1);

namespace Site\Location\DomainModel\Dto;

final readonly class CityDto implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public string $transcription,
        public string $area,
    ) {
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'transcription' => $this->transcription,
            'address' => $this->area,
        ];
    }
}
