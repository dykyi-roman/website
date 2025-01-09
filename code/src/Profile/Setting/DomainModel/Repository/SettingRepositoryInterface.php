<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Repository;

use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Model\Setting;
use Profile\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

interface SettingRepositoryInterface
{
    public function findByName(UserId $id, PropertyName $name): ?Setting;

    /**
     * @return array<Setting>
     */
    public function findAll(UserId $id): array;

    public function updateProperties(UserId $id, Property ...$properties): void;
}
