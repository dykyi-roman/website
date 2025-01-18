<?php

declare(strict_types=1);

namespace Site\Location\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;
use Site\Location\DomainModel\Dto\CityDto;

final readonly class DictionaryOfCitiesJsonResponder implements ResponderInterface
{
    public function __construct(
        /** @var array<CityDto> $cities */
        private array $cities,
    ) {
    }

    public function respond(): self
    {
        return $this;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return array_map(
            static fn (CityDto $cityDto) => [
                'name' => $cityDto->name,
                'transcription' => $cityDto->transcription,
                'address' => $cityDto->area,
            ],
            $this->cities,
        );
    }

    public function statusCode(): int
    {
        return 200;
    }
}
