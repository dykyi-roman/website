<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\Repository;

use EventStorage\DomainModel\Exception\DuplicateEventException;
use EventStorage\DomainModel\Model\Event;
use EventStorage\DomainModel\ValueObject\EventId;

interface EventRepositoryInterface
{
    /**
     * @throws DuplicateEventException
     */
    public function save(Event $event): void;

    public function findById(EventId $id): ?Event;

    /**
     * @return array<Event>
     */
    public function findByPriority(int $limit = 10, int $offset = 0): array;

    /**
     * @return array<Event>
     */
    public function findByModelId(string $modelId, int $limit = 10, int $offset = 0): array;

    /**
     * Archive events older than the specified date.
     */
    public function archiveEvents(\DateTimeImmutable $olderThan): void;

    /**
     * Delete archived events older than the specified date.
     */
    public function deleteArchivedEvents(\DateTimeImmutable $olderThan): void;
}
