<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Doctrine\DoctrineType;

use App\Partner\DomainModel\Enum\PartnerStatus;
use App\Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;

final class ClientStatusType extends IntEnumType
{
    protected const string ID_TYPE = 'client_status';
    protected const string ID_CLASSNAME = PartnerStatus::class;
}
