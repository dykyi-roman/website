<?php

declare(strict_types=1);

namespace Site\User\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Enum\UserId;
use Site\User\DomainModel\Model\User;
use Site\User\DomainModel\Model\UserInterface;
use Site\User\DomainModel\Repository\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    /** @var EntityRepository<User> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    public function save(UserInterface $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findById(UserId $id): ?UserInterface
    {
        /** @var UserInterface|null */
        return $this->repository->find($id->toRfc4122());
    }

    public function findByEmail(Email $email): ?UserInterface
    {
        /** @var UserInterface|null */
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByToken(string $token): ?UserInterface
    {
        /** @var UserInterface|null */
        return $this->repository->findOneBy(['token' => $token]);
    }

    public function isEmailUnique(Email $email): bool
    {
        return null === $this->findByEmail($email);
    }
}
