<?php

declare(strict_types=1);

namespace Site\Setting\DomainModel\Repository;

use Site\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

interface SettingRepositoryInterface
{
    public function updateProperty(UserId $id, Property $property): void;
}