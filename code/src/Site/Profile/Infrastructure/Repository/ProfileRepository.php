<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\Profile\DomainModel\Model\Profile;
use Site\Profile\DomainModel\Enum\PropertyGroup;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Repository\ProfileRepositoryInterface;
use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

final class ProfileRepository implements ProfileRepositoryInterface
{
    /** @var EntityRepository<Profile> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $eventBus,
    ) {
        $this->repository = $this->entityManager->getRepository(Profile::class);
    }

    public function updateSettingProperty(UserId $id, Property $property): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $profile = $qb->select('p')
            ->from(Profile::class, 'p')
            ->where('p.id = :id')
            ->andWhere('p.group = :group')
            ->andWhere('p.name = :name')
            ->setParameter('id', $id->toBinary())
            ->setParameter('group', $property->group->value)
            ->setParameter('name', $property->name->value)
            ->getQuery()
            ->getOneOrNullResult();

        if ($profile === null) {
            $profile = new Profile($id, $property);
        } else {
            $profile->changeProperty($property);
        }

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        foreach ($profile->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
