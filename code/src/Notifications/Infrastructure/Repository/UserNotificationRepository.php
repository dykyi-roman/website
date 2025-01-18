<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Notifications\DomainModel\Enum\UserNotificationId;
use Notifications\DomainModel\Exception\NotificationNotFoundException;
use Notifications\DomainModel\Model\UserNotification;
use Notifications\DomainModel\Repository\UserNotificationRepositoryInterface;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\Dto\PaginationDto;

final class UserNotificationRepository implements UserNotificationRepositoryInterface
{
    /** @var EntityRepository<UserNotification> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(UserNotification::class);
    }

    public function getUserNotifications(UserId $userId, int $page = 1, int $perPage = 20): PaginationDto
    {
        /** @var list<UserNotification> $result */
        $result = $this->repository->createQueryBuilder('un')
            ->andWhere('un.userId = :userId')
            ->andWhere('un.isDeleted is NULL')
            ->setParameter('userId', $userId->toRfc4122())
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
            ->andWhere('un.isRead is NULL')
            ->andWhere('un.isDeleted is NULL')
            ->setParameter('userId', $userId->toRfc4122())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(UserNotification $userNotification): void
    {
        $this->entityManager->persist($userNotification);
        $this->entityManager->flush();
    }

    public function findById(UserNotificationId $id): UserNotification
    {
        $result = $this->repository->find($id->toRfc4122());
        if (null === $result) {
            throw new NotificationNotFoundException($id);
        }

        return $result;
    }
}
