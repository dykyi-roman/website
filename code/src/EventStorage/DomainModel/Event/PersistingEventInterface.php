<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\Event;

use Shared\DomainModel\Event\DomainEventInterface;

interface PersistingEventInterface extends DomainEventInterface
{
    public function getAggregateId(): string;

    public function getAggregateType(): string;

    public function getOccurredOn(): \DateTimeImmutable;

    /** @return array<string, string> */
    public function getPayload(): array;

    public function getVersion(): int;

    public function getPriority(): int;
}
