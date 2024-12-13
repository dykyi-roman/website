<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

final readonly class Email implements \Stringable
{
    private function __construct(private string $value)
    {
        $this->validate($value);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function validate(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
