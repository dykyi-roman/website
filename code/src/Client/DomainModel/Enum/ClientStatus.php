<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Enum;

enum ClientStatus: int
{
    case ACTIVE = 1;
    case DEACTIVATED = 0;
}