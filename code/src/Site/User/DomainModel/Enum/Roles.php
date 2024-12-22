<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Enum;

enum Roles: string
{
    case ROLE_CLIENT = 'ROLE_CLIENT';
    case ROLE_PARTNER = 'ROLE_PARTNER';
}
