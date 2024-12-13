<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\Doctrine\DoctrineType;

use App\Partner\DomainModel\Enum\PartnerStatus;
use App\Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;

final class PartnerStatusType extends IntEnumType
{
    protected const string TYPE_NAME = 'partner_status';
    protected const string CLASS_NAME = PartnerStatus::class;
}
