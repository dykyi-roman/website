<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Repository;

use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

interface SettingRepositoryInterface
{
    public function updateProperty(UserId $id, Property $property): void;
}