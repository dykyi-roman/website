<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\Profile\DomainModel\Enum\SettingId;
use Site\Profile\DomainModel\Model\Setting;
use Site\Profile\DomainModel\Repository\ProfileRepositoryInterface;
use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

final class ProfileRepository implements ProfileRepositoryInterface
{
    /** @var EntityRepository<Setting> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $eventBus,
    ) {
        $this->repository = $this->entityManager->getRepository(Setting::class);
    }

    public function updateSettingProperty(UserId $id, Property $property): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        /** @var null|Setting $profile */
        $profile = $qb->select('p')
            ->from(Setting::class, 'p')
            ->where('p.userId = :id')
            ->andWhere('p.category = :category')
            ->andWhere('p.name = :name')
            ->setParameter('id', $id->toBinary())
            ->setParameter('category', $property->category->value)
            ->setParameter('name', $property->name->value)
            ->getQuery()
            ->getOneOrNullResult();

        if ($profile === null) {
            $profile = new Setting(new SettingId(), $id, $property);
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
