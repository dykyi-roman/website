<?php

declare(strict_types=1);

namespace App\Partner\DomainModel\Enum;

use Symfony\Component\Uid\Uuid;

final class PartnerId extends Uuid
{
    public function __construct()
    {
        parent::__construct(Uuid::v4()->toRfc4122());
    }
}