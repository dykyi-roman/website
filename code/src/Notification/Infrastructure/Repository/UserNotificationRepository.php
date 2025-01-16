<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notification\DomainModel\Enum\UserNotificationId;
use Notification\DomainModel\Model\UserNotification;
use Notification\DomainModel\Repository\UserNotificationRepositoryInterface;

final class UserNotificationRepository implements UserNotificationRepositoryInterface
{
    /** @var EntityRepository<Event> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(UserNotification::class);
    }

    public function save(UserNotification $event): void
    {
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    public function findById(UserNotificationId $id): ?UserNotification
    {
        /* @var UserNotification|null */
        return $this->repository->find($id->toRfc4122());
    }
}
