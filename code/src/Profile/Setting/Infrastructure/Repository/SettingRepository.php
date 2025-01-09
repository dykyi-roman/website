<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\Services\MessageBusInterface;
use Profile\Setting\DomainModel\Enum\SettingId;
use Profile\Setting\DomainModel\Model\Setting;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

final class SettingRepository implements SettingRepositoryInterface
{
    /** @var EntityRepository<Setting> */
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $eventBus,
    ) {
        $this->repository = $this->entityManager->getRepository(Setting::class);
    }

    public function updateProperty(UserId $id, Property $property): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        /** @var null|Setting $Setting */
        $Setting = $qb->select('p')
            ->from(Setting::class, 'p')
            ->where('p.userId = :id')
            ->andWhere('p.category = :category')
            ->andWhere('p.name = :name')
            ->setParameter('id', $id->toBinary())
            ->setParameter('category', $property->category->value)
            ->setParameter('name', $property->name->value)
            ->getQuery()
            ->getOneOrNullResult();

        if ($Setting === null) {
            $Setting = new Setting(new SettingId(), $id, $property);
        } else {
            $Setting->changeProperty($property);
        }

        $this->entityManager->persist($Setting);
        $this->entityManager->flush();

        foreach ($Setting->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
