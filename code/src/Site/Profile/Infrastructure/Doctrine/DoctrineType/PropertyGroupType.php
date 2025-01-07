<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Site\Profile\DomainModel\Enum\PropertyGroup;

final class PropertyGroupType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_group';
    protected const ?string CLASS_NAME = PropertyGroup::class;
}
