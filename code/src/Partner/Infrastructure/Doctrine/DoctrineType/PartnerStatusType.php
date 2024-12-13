<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\Doctrine\DoctrineType;

use App\Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;
use App\Partner\DomainModel\Enum\PartnerStatus;

final class PartnerStatusType extends IntEnumType
{
    protected const string TYPE_NAME = 'partner_status';
    protected const string CLASS_NAME = PartnerStatus::class;
}