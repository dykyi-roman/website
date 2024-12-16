<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Country implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public string $code,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new InvalidArgumentException('Country name cannot be empty');
        }

        if (empty(trim($this->code))) {
            throw new InvalidArgumentException('Country code cannot be empty');
        }

        if (strlen($this->code) !== 2) {
            throw new InvalidArgumentException('Country code must be exactly 2 characters long (ISO 3166-1 alpha-2)');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}