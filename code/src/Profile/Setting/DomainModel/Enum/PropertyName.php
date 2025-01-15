<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Enum;

enum PropertyName: string
{
    case ACCEPTED_COOKIES = 'accepted_cookies';

    case SETTINGS_GENERAL_LANGUAGE = 'language';
    case SETTINGS_GENERAL_CURRENCY = 'currency';
    case SETTINGS_GENERAL_THEME = 'theme';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
