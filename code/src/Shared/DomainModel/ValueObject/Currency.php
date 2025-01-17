<?php

declare(strict_types=1);

namespace Shared\DomainModel\ValueObject;

final readonly class Currency implements \Stringable
{
    private const array VALID_CURRENCIES = [
        'USD', 'EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'CHF', 'CNY', 'HKD', 'NZD'
    ];

    private function __construct(
        private string $code,
    ) {
        if (3 !== strlen($code)) {
            throw new \InvalidArgumentException('Currency code must be exactly 3 characters long');
        }

        if (!ctype_upper($code)) {
            throw new \InvalidArgumentException('Currency code must be in uppercase letters');
        }

        if (!in_array($code, self::VALID_CURRENCIES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid currency code "%s". Valid codes are: %s', $code, implode(', ', self::VALID_CURRENCIES)));
        }
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function code(): string
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
            default => $this->code,
        };
    }
}
