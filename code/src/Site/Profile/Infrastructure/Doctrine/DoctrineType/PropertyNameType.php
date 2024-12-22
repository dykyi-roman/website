<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Site\Profile\DomainModel\Enum\PropertyName;

final class PropertyNameType extends StringEnumType
{
    protected const string TYPE_NAME = 'property_name';
    protected const string CLASS_NAME = PropertyName::class;
}
