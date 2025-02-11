<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class City implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public string $transcription,
        public ?string $address = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('City name cannot be empty');
        }

        if (empty(trim($this->transcription))) {
            throw new \InvalidArgumentException('City transcription cannot be empty');
        }

        if (null !== $this->address && empty(trim($this->address))) {
            throw new \InvalidArgumentException('If address is provided, it cannot be empty');
        }
    }

    /** @return array{name: string, transcription: string, address: string|null} */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'transcription' => $this->transcription,
            'address' => $this->address,
        ];
    }
}
