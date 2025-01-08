<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Site\Profile\DomainModel\Enum\PropertyCategory;

final class PropertyCategoryType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_category';
    protected const ?string CLASS_NAME = PropertyCategory::class;
}
