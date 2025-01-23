<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Persistence\Doctrine\Type;

use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Shared\Infrastructure\Persistence\Doctrine\Type\StringEnumType;

final class PropertyCategoryType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_category';
    protected const ?string CLASS_NAME = PropertyCategory::class;
}
