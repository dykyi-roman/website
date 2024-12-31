<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class Currency implements \Stringable
{
    private function __construct(
        private string $code,
    ) {
        if (3 !== strlen($code)) {
            throw new \InvalidArgumentException('Currency code must be exactly 3 characters long');
        }

        if (!ctype_upper($code)) {
            throw new \InvalidArgumentException('Currency code must be in uppercase letters');
        }
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function symbol(): string
    {
        return match ($this->code) {
            'USD' => '$',
            'EUR' => '€',
        };
    }
}
