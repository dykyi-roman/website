<?php

declare(strict_types=1);

namespace Site\Setting\DomainModel\Enum;

enum PropertyCategory: string
{
    case GENERAL = 'GENERAL';
    case ACCOUNT = 'ACCOUNT';
    case NOTIFICATION = 'NOTIFICATION';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
