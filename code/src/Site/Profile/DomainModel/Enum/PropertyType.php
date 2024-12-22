<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Enum;

enum PropertyType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case BOOL = 'bool';
    case DATE = 'date';
}
