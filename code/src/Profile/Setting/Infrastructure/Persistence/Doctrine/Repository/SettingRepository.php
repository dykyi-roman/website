<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Model\Setting;
use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Profile\Setting\DomainModel\ValueObject\Property;
use Profile\Setting\DomainModel\ValueObject\SettingId;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;

final readonly class SettingRepository implements SettingRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function findByName(UserId $id, PropertyName $name): ?Setting
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from(Setting::class, 's')
            ->andWhere('s.userId = :id')
            ->andWhere('s.name = :name')
            ->setParameter('id', $id->toBinary())
            ->setParameter('name', $name->value);

        /** @var Setting|null $result */
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * @return array<int, Setting>
     */
    public function findAll(UserId $id): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from(Setting::class, 's')
            ->andWhere('s.userId = :id')
            ->setParameter('id', $id->toBinary());

        /** @var array<int, Setting> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function updateProperties(UserId $id, Property ...$properties): void
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('s')
            ->from(Setting::class, 's')
            ->where('s.userId = :id')
            ->setParameter('id', $id->toBinary());

        if (!empty($properties)) {
            $orX = $qb->expr()->orX();
            foreach ($properties as $prop) {
                $categoryParam = 'category_'.$prop->category->value;
                $nameParam = 'name_'.$prop->name->value;

                $orX->add(
                    $qb->expr()->andX(
                        $qb->expr()->eq('s.category', ':'.$categoryParam),
                        $qb->expr()->eq('s.name', ':'.$nameParam)
                    )
                );

                $qb->setParameter($categoryParam, $prop->category->value)
                   ->setParameter($nameParam, $prop->name->value);
            }
            $qb->andWhere($orX);
        }

        /** @var iterable<Setting> $existingSettings */
        $existingSettings = $qb->getQuery()->getResult();

        // Index existing settings by category and name for quick lookup
        /** @var array<string, Setting> */
        $settingsMap = [];
        foreach ($existingSettings as $setting) {
            $key = $setting->getProperty()->category->value.'_'.$setting->getProperty()->name->value;
            $settingsMap[$key] = $setting;
        }

        $events = [];
        // Process all properties in batch
        foreach ($properties as $property) {
            $key = $property->category->value.'_'.$property->name->value;

            if (isset($settingsMap[$key])) {
                $settingsMap[$key]->changeProperty($property);
                $events = array_merge($events, $settingsMap[$key]->releaseEvents());
            } else {
                $setting = new Setting(new SettingId(), $id, $property);
                $this->entityManager->persist($setting);
                $events = array_merge($events, $setting->releaseEvents());
            }
        }

        $this->entityManager->flush();

        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
