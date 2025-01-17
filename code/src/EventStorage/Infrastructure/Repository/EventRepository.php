<?php

declare(strict_types=1);

namespace EventStorage\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EventStorage\DomainModel\Enum\EventId;
use EventStorage\DomainModel\Exception\DuplicateEventException;
use EventStorage\DomainModel\Model\Event;
use EventStorage\DomainModel\Repository\EventRepositoryInterface;

final class EventRepository implements EventRepositoryInterface
{
    private const int BATCH_SIZE = 100;

    /** @var EntityRepository<Event> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Event::class);
    }

    /**
     * @throws DuplicateEventException
     */
    public function save(Event $event): void
    {
        if ($this->isDuplicate($event->getModelId(), $event->getType(), $event->getOccurredOn())) {
            throw new DuplicateEventException();
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    public function findById(EventId $id): ?Event
    {
        /* @var Event|null */
        return $this->repository->find($id->toRfc4122());
    }

    /**
     * @param array<Event> $events
     */
    public function saveBatch(array $events): void
    {
        $batchSize = 0;

        foreach ($events as $event) {
            if ($this->isDuplicate($event->getModelId(), $event->getType(), $event->getOccurredOn())) {
                continue;
            }

            $this->entityManager->persist($event);

            if (self::BATCH_SIZE === ++$batchSize) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $batchSize = 0;
            }
        }

        if ($batchSize > 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * @return array<Event>
     */
    public function findByPriority(int $limit = 10, int $offset = 0): array
    {
        /** @var array<Event> $result */
        $result = $this->repository->createQueryBuilder('e')
            ->where('e.archived = false')
            ->orderBy('e.priority', 'DESC')
            ->addOrderBy('e.occurredOn', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return array<Event>
     */
    public function findByModelId(string $modelId, int $limit = 10, int $offset = 0): array
    {
        /** @var array<Event> $result */
        $result = $this->repository->createQueryBuilder('e')
            ->where('e.modelId = :modelId')
            ->andWhere('e.archived = false')
            ->setParameter('modelId', $modelId)
            ->orderBy('e.occurredOn', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function archiveEvents(\DateTimeImmutable $olderThan): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(Event::class, 'e')
            ->set('e.archived', true)
            ->where('e.occurredOn <= :olderThan')
            ->setParameter('olderThan', $olderThan)
            ->getQuery()
            ->execute();
    }

    public function deleteArchivedEvents(\DateTimeImmutable $olderThan): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(Event::class, 'e')
            ->where('e.archived = true')
            ->andWhere('e.occurredOn <= :olderThan')
            ->setParameter('olderThan', $olderThan)
            ->getQuery()
            ->execute();
    }

    public function isDuplicate(string $modelId, string $type, \DateTimeImmutable $occurredOn): bool
    {
        $result = $this->repository->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.modelId = :modelId')
            ->andWhere('e.type = :type')
            ->andWhere('e.occurredOn = :occurredOn')
            ->setParameter('modelId', $modelId)
            ->setParameter('type', $type)
            ->setParameter('occurredOn', $occurredOn)
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }
}
