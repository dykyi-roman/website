<?php

declare(strict_types=1);

namespace Profile\User\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\UserNotFoundException;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;

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
    public function findById(UserId $userId): UserInterface
    {
        $user = $this->repository->find($userId->toRfc4122());
        if (null === $user) {
            throw new UserNotFoundException($userId);
        }

        return $user;
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

    /**
     * @return UserId[]
     */
    public function findAll(): array
    {
        return array_map(
            static fn (UserInterface $user): UserId => UserId::fromString($user->getId()),
            $this->repository->findAll(),
        );
    }
}
