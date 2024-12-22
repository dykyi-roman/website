<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Site\Profile\DomainModel\Enum\PropertyType;

final class PropertyTypeType extends StringEnumType
{
    protected const string TYPE_NAME = 'property_type';
    protected const string CLASS_NAME = PropertyType::class;
}
