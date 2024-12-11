<?php

declare(strict_types=1);

namespace App\Client\DomainModel\Enum;

use Symfony\Component\Uid\Uuid;

final class ClientId extends Uuid
{
    public function __construct()
    {
        parent::__construct(Uuid::v4()->toRfc4122());
    }
}