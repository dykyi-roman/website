<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Repository;

use App\Client\DomainModel\Enum\ClientId;
use App\Client\DomainModel\Model\Client;
use App\Client\DomainModel\Repository\ClientRepositoryInterface;
use App\Shared\Domain\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class ClientRepository implements ClientRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Client::class);
    }

    public function save(Client $client): void
    {
        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }

    public function findById(ClientId $id): ?Client
    {
        return $this->repository->find($id->toRfc4122());
    }

    public function findByEmail(Email $email): ?Client
    {
        return $this->repository->findOneBy(['email' => $email->value]);
    }
}
