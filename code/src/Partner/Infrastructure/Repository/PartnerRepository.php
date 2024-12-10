<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\Repository;

use App\Partner\DomainModel\Model\Partner;
use App\Partner\DomainModel\Repository\PartnerRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PartnerRepository implements PartnerRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->repository = $this->entityManager->getRepository(Partner::class);
    }

    public function save(Partner $partner): void
    {
        $this->entityManager->persist($partner);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Partner
    {
        return $this->repository->find($id);
    }
}
