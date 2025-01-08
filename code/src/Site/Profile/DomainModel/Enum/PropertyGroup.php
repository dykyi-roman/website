<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Enum;

enum PropertyGroup: string
{
    case GENERAL = 'GENERAL';
    case ACCOUNT = 'ACCOUNT';
    case NOTIFICATION = 'NOTIFICATION';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
