<?php

declare(strict_types=1);

namespace Site\Registration\DomainModel\Event;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use Shared\DomainModel\ValueObject\UserId;

final readonly class UserChangedPasswordEvent implements PersistingEventInterface
{
    public function __construct(
        public UserId $id,
    ) {
    }

    public function getAggregateId(): string
    {
        return $this->id->toRfc4122();
    }

    public function getAggregateType(): string
    {
        return 'user.restored.password';
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /** @return array<string, string> */
    public function getPayload(): array
    {
        return [];
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
