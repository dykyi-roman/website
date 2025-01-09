<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Enum;

enum PropertyName: string
{
    case PHONE_VERIFIED_AT = 'phone_verified_at';
    case EMAIL_VERIFIED_AT = 'email_verified_at';
    case ACCEPTED_COOKIES = 'accepted_cookies';

    case Setting_SETTINGS_GENERAL_LANGUAGE = 'language';
    case Setting_SETTINGS_GENERAL_CURRENCY = 'currency';
    case Setting_SETTINGS_GENERAL_THEME = 'theme';

    /** @return array<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
