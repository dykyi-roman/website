<?php

declare(strict_types=1);

namespace EventStorage\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EventStorage\DomainModel\Enum\EventId;
use EventStorage\DomainModel\Model\Event;
use EventStorage\DomainModel\Repository\EventRepositoryInterface;

final class EventRepository implements EventRepositoryInterface
{
    /** @var EntityRepository<Event> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Event::class);
    }

    public function save(Event $event): void
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    public function findById(EventId $id): ?Event
    {
        /* @var Event|null */
        return $this->repository->find($id->toRfc4122());
    }
}
