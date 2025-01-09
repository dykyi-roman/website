<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Profile\Setting\DomainModel\Enum\PropertyName;

final class PropertyNameType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_name';
    protected const ?string CLASS_NAME = PropertyName::class;
}
