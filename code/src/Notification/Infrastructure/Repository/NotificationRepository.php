<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Model\Notification;
use Notification\DomainModel\Repository\NotificationRepositoryInterface;

final class NotificationRepository implements NotificationRepositoryInterface
{
    /** @var EntityRepository<Event> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Notification::class);
    }

    public function save(Notification $event): void
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    public function findById(NotificationId $id): ?Notification
    {
        /* @var Notification|null */
        return $this->repository->find($id->toRfc4122());
    }
}
