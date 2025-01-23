<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Persistence\Doctrine\Type;

use Profile\Setting\DomainModel\Enum\PropertyName;
use Shared\Infrastructure\Persistence\Doctrine\Type\StringEnumType;

final class PropertyNameType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'property_name';
    protected const ?string CLASS_NAME = PropertyName::class;
}
