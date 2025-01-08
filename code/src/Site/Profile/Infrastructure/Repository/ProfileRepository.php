<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\Profile\DomainModel\Model\Profile;
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

    public function сhangeSettingProperty(UserId $id, Property $property): void
    {
        $profile = $this->repository->find($id);
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
