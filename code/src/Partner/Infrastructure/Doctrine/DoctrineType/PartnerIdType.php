<?php

declare(strict_types=1);

namespace App\Partner\Infrastructure\Doctrine\DoctrineType;

use App\Partner\DomainModel\Enum\PartnerId;
use App\Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class PartnerIdType extends UuidType
{
    protected const string ID_TYPE = 'partner_id';
    protected const string ID_CLASSNAME = PartnerId::class;
}
