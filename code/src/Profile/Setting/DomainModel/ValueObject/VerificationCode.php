<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\ValueObject;

final readonly class VerificationCode implements \Stringable
{
    private function __construct(
        private string $value,
    ) {
        if (6 !== strlen($value) || !ctype_digit($value)) {
            throw new \InvalidArgumentException('Verification code must be 6 digits');
        }
    }

    public static function generate(): self
    {
        return new self(str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT));
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
