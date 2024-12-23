<?php

declare(strict_types=1);

namespace Site\Dashboard\DomainModel\ValueObject;

final readonly class Vote
{
    private function __construct(
        private int $count,
        private string $appType,
    ) {
    }

    public static function create(int $count, string $appType): self
    {
        return new self($count, $appType);
    }

    public static function zero(string $appType): self
    {
        return new self(0, $appType);
    }

    public function increment(): self
    {
        return new self($this->count + 1, $this->appType);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getAppType(): string
    {
        return $this->appType;
    }
}
