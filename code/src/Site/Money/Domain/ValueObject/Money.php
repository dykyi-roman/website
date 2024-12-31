<?php

declare(strict_types=1);

namespace Site\Money\Domain\ValueObject;

final readonly class Money
{
    private function __construct(
        private float $amount,
        private Currency $currency,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function create(float $amount, Currency $currency): self
    {
        return new self($amount, $currency);
    }

    public static function fromPrimitives(float $amount, string $currencyCode): self
    {
        return new self($amount, Currency::fromString($currencyCode));
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        if (!$this->currency->equals($other->currency)) {
            throw new \InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Multiplier cannot be negative');
        }

        return new self($this->amount * $multiplier, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency->equals($other->currency);
    }
}
