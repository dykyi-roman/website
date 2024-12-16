<?php

declare(strict_types=1);

namespace App\Shared\DomainModel\ValueObject;

use InvalidArgumentException;

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
            throw new InvalidArgumentException('City name cannot be empty');
        }

        if (empty(trim($this->transcription))) {
            throw new InvalidArgumentException('City transcription cannot be empty');
        }

        if ($this->address !== null && empty(trim($this->address))) {
            throw new InvalidArgumentException('If address is provided, it cannot be empty');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'transcription' => $this->transcription,
            'address' => $this->address,
        ];
    }
}