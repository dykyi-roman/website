<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class Location implements \JsonSerializable
{
    public function __construct(
        public ?Country $country = null,
        public ?City $city = null,
    ) {
    }

    /**
     * @return array{country: array{code: string}|null, city: array{name: string, transcription: string, address: string|null}|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'country' => $this->country?->jsonSerialize(),
            'city' => $this->city?->jsonSerialize(),
        ];
    }
}
