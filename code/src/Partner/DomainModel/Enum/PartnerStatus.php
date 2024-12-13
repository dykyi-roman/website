<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Enum;

enum PartnerStatus: int
{
    case ACTIVE = 1;
    case DEACTIVATED = 0;
}
