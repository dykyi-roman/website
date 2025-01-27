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

    public function save(UserStatus $userStatus): void
    {
        $this->entityManager->persist($userStatus);
        $this->entityManager->flush();
    }

    public function findByUserId(UserId $userId): ?UserStatus
    {
        /* @var UserStatus|null */
        return $this->repository->find($userId->toRfc4122());
    }
}