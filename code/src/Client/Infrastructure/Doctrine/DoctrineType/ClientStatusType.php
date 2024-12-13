<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Doctrine\DoctrineType;

use App\Client\DomainModel\Enum\ClientStatus;
use App\Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;

final class ClientStatusType extends IntEnumType
{
    protected const string TYPE_NAME = 'client_status';
    protected const string CLASS_NAME = ClientStatus::class;
}
