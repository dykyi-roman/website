<?php

declare(strict_types=1);

namespace Profile\UserStatus\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\ValueObject\UserId;

final class UserStatusRepository implements UserStatusRepositoryInterface
{
    /** @var EntityRepository<UserStatus> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(UserStatus::class);
    }

    public function saveOrUpdate(UserStatus ...$userStatuses): void
    {
        if (empty($userStatuses)) {
            return;
        }

        $userIds = array_map(
            static fn (UserStatus $status): string => $status->getUserId()->toRfc4122(),
            $userStatuses
        );

        /** @var UserStatus[] $existingStatuses */
        $existingStatuses = $this->repository->createQueryBuilder('us')
            ->andWhere('us.userId IN (:userIds)')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();

        $existingStatusMap = [];
        foreach ($existingStatuses as $status) {
            $existingStatusMap[$status->getUserId()->toRfc4122()] = $status;
        }

        foreach ($userStatuses as $userStatus) {
            $userId = $userStatus->getUserId()->toRfc4122();

            if (isset($existingStatusMap[$userId])) {
                $existingStatusMap[$userId]->updateStatus(
                    isOnline: $userStatus->isOnline(),
                    lastActivityAt: $userStatus->getLastOnlineAt()
                );
            } else {
                $this->entityManager->persist($userStatus);
            }
        }

        $this->entityManager->flush();
    }

    public function findByUserId(UserId $userId): ?UserStatus
    {
        /* @var UserStatus|null */
        return $this->repository->find($userId->toRfc4122());
    }

    /**
     * @return UserStatus[]
     */
    public function findAllOnline(): array
    {
        /** @var UserStatus[] $result */
        $result = $this->entityManager->createQueryBuilder()
            ->select('us')
            ->from(UserStatus::class, 'us')
            ->andWhere('us.isOnline = :status')
            ->setParameter('status', true)
            ->getQuery()
            ->getResult();

        return $result;
    }
}
