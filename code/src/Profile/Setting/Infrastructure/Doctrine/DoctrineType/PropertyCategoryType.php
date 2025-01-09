<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;
use Profile\Setting\DomainModel\Enum\PropertyCategory;

final class PropertyCategoryType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_category';
    protected const ?string CLASS_NAME = PropertyCategory::class;
}
