<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notifications\DomainModel\Exception\UserNotificationNotFoundException;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Notifications\DomainModel\ValueObject\UserNotificationId;
use Shared\DomainModel\Dto\PaginationDto;
use Shared\DomainModel\ValueObject\UserId;

final class UserNotificationRepository implements UserNotificationRepositoryInterface
{
    /** @var EntityRepository<UserNotification> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(UserNotification::class);
    }

    /**
     * @return PaginationDto<UserNotification>
     */
    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto
    {
        /** @var array<array-key, UserNotification> $result */
        $result = $this->repository->createQueryBuilder('un')
            ->andWhere('un.userId = :userId')
            ->andWhere('un.deletedAt is NULL')
            ->setParameter('userId', $userId->toBinary())
            ->orderBy('un.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return new PaginationDto(
            items: $result,
            page: $page,
            limit: $perPage,
        );
    }

    public function getUnreadCount(UserId $userId): int
    {
        return (int) $this->repository->createQueryBuilder('un')
            ->select('COUNT(un.id)')
            ->andWhere('un.userId = :userId')
            ->andWhere('un.readAt is NULL')
            ->andWhere('un.deletedAt is NULL')
            ->setParameter('userId', $userId->toBinary())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(UserNotification ...$userNotifications): void
    {
        foreach ($userNotifications as $userNotification) {
            $this->entityManager->persist($userNotification);
        }
        $this->entityManager->flush();
    }

    public function markAllAsDeleted(UserId $userId): void
    {
        $this->repository->createQueryBuilder('un')
            ->update()
            ->set('un.deletedAt', ':now')
            ->andWhere('un.userId = :userId')
            ->andWhere('un.deletedAt is NULL')
            ->setParameter('userId', $userId->toBinary())
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function markAllAsRead(UserId $userId): void
    {
        $this->repository->createQueryBuilder('un')
            ->update()
            ->set('un.readAt', ':now')
            ->andWhere('un.userId = :userId')
            ->andWhere('un.readAt is NULL')
            ->andWhere('un.deletedAt is NULL')
            ->setParameter('userId', $userId->toBinary())
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    /**
     * @throws UserNotificationNotFoundException
     */
    public function findById(UserNotificationId $id): UserNotification
    {
        $result = $this->repository->find($id);
        if (null === $result) {
            throw new UserNotificationNotFoundException($id);
        }

        return $result;
    }
}
