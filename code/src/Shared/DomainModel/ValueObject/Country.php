<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class Country implements \JsonSerializable
{
    public function __construct(
        public string $code,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->code))) {
            throw new \InvalidArgumentException('Country code cannot be empty');
        }

        if (2 !== strlen($this->code)) {
            throw new \InvalidArgumentException('Country code must be exactly 2 characters long (ISO 3166-1 alpha-2)');
        }
    }

    /** @return array{code: string} */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
        ];
    }
}
