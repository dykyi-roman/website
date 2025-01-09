<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Repository;

use Profile\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

interface SettingRepositoryInterface
{
    public function updateProperty(UserId $id, Property $property): void;
}