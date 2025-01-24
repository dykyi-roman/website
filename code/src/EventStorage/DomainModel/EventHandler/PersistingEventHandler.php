<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\EventHandler;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use EventStorage\DomainModel\Model\Event;
use EventStorage\DomainModel\Repository\EventRepositoryInterface;
use EventStorage\DomainModel\ValueObject\EventId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class PersistingEventHandler
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(PersistingEventInterface $event): void
    {
        $this->eventRepository->save(
            new Event(
                new EventId(),
                $event->getAggregateId(),
                $event->getAggregateType(),
                $event->getPayload(),
                $event->getOccurredOn(),
                $event->getVersion(),
                $event->getPriority()
            )
        );
    }
}
