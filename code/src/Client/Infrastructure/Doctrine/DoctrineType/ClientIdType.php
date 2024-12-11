<?php

declare(strict_types=1);

namespace App\Client\Infrastructure\Doctrine\DoctrineType;

use App\Client\DomainModel\Enum\ClientId;
use App\Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class ClientIdType extends UuidType
{
    protected const string ID_TYPE = 'client_id';
    protected const string ID_CLASSNAME = ClientId::class;
}
