<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\Notification;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\NotificationRepositoryInterface;
use Notifications\DomainModel\ValueObject\NotificationId;

final readonly class NotificationRepository implements NotificationRepositoryInterface
{
    /** @var EntityRepository<Notification> */
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Notification::class);
    }

    public function save(Notification ...$notifications): void
    {
        foreach ($notifications as $notification) {
            $this->entityManager->persist($notification);
        }
        $this->entityManager->flush();
    }

    /**
     * @throws NotificationNotFoundException
     */
    public function findById(NotificationId $id): Notification
    {
        $result = $this->repository->createQueryBuilder('n')
            ->andWhere('n.id = :id')
            ->setParameter('id', $id->toBinary())
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $result) {
            throw new NotificationNotFoundException($id);
        }

        return $result;
    }
}