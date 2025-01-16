<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Repository;

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

    public function save(Notification $notification): void
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function findById(NotificationId $id): ?Notification
    {
        /* @var Notification|null */
        return $this->repository->find($id->toRfc4122());
    }

    public function getMassNotifications(\DateTimeImmutable $since): array
    {
        return $this->repository->createQueryBuilder('n')
            ->where('n.createdAt >= :since')
            ->andWhere('n.isMass = true')
            ->setParameter('since', $since)
            ->getQuery()
            ->getResult();
    }

    public function getActiveNotifications(): array
    {
        return $this->repository->createQueryBuilder('n')
            ->where('n.isActive = true')
            ->getQuery()
            ->getResult();
    }
}
