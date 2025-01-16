<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notification\DomainModel\Enum\NotificationId;
use Notification\DomainModel\Enum\UserNotificationId;
use Notification\DomainModel\Model\UserNotification;
use Notification\DomainModel\Repository\UserNotificationRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;

final class UserNotificationRepository implements UserNotificationRepositoryInterface
{
    /** @var EntityRepository<UserNotification> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(UserNotification::class);
    }

    public function findByUserId(UserId $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->repository->createQueryBuilder('un')
            ->where('un.userId = :userId')
            ->andWhere('un.isDeleted = false')
            ->setParameter('userId', $userId->toRfc4122())
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->orderBy('un.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUnreadCount(UserId $userId): int
    {
        return (int) $this->repository->createQueryBuilder('un')
            ->select('COUNT(un.id)')
            ->where('un.userId = :userId')
            ->andWhere('un.isRead = false')
            ->andWhere('un.isDeleted = false')
            ->setParameter('userId', $userId->toRfc4122())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAsRead(UserId $userId, NotificationId $notificationId): void
    {
        $this->repository->createQueryBuilder('un')
            ->update()
            ->set('un.isRead', true)
            ->where('un.userId = :userId')
            ->andWhere('un.notificationId = :notificationId')
            ->setParameters([
                'userId' => $userId->toRfc4122(),
                'notificationId' => $notificationId->toRfc4122(),
            ])
            ->getQuery()
            ->execute();
    }

    public function markAsDeleted(UserId $userId, NotificationId $notificationId): void
    {
        $this->repository->createQueryBuilder('un')
            ->update()
            ->set('un.isDeleted', true)
            ->where('un.userId = :userId')
            ->andWhere('un.notificationId = :notificationId')
            ->setParameters([
                'userId' => $userId->toRfc4122(),
                'notificationId' => $notificationId->toRfc4122(),
            ])
            ->getQuery()
            ->execute();
    }

    public function save(UserNotification $userNotification): void
    {
        $this->entityManager->persist($userNotification);
        $this->entityManager->flush();
    }

    public function findById(UserNotificationId $id): ?UserNotification
    {
        /* @var UserNotification|null */
        return $this->repository->find($id->toRfc4122());
    }
}
