<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Repository;

use Site\Profile\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

interface ProfileRepositoryInterface
{
    public function updateSettingProperty(UserId $id, Property $property): void;
}