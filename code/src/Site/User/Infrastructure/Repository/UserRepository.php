<?php

declare(strict_types=1);

namespace Site\User\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\Services\MessageBusInterface;
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
        private readonly MessageBusInterface $eventBus,
    ) {
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    public function save(UserInterface $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        foreach ($user->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\UserNotFoundException
     */
    public function findById(UserId $id): UserInterface
    {
        return $this->repository->find($id->toRfc4122());
    }

    public function findByEmail(Email $email): ?UserInterface
    {
        /* @var UserInterface|null */
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findByToken(string $field, string $token): ?UserInterface
    {
        /* @var UserInterface|null */
        return $this->repository->findOneBy([$field => $token]);
    }

    public function isEmailUnique(Email $email): bool
    {
        return null === $this->findByEmail($email);
    }
}
