<?php

declare(strict_types=1);

namespace Shared\DomainModel\Model;

use Shared\DomainModel\Event\DomainEventInterface;

abstract class AbstractDomainModel implements DomainModelInterface
{
    /** @var array<int, DomainEventInterface> */
    protected array $domainEvents = [];

    public function raise(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * @return DomainEventInterface[]
     */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
