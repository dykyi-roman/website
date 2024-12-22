<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Enum;

enum PropertyName: string
{
    case PHONE_VERIFIED_AT = 'phone_verified_at';
    case EMAIL_VERIFIED_AT = 'email_verified_at';
    case ACCEPTED_COOKIES = 'accepted_cookies';
}
