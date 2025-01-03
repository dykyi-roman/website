<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Enum;

use Symfony\Component\Uid\Uuid;

final class UserId extends Uuid
{
    public function __construct(?string $uuid = null, bool $checkVariant = false)
    {
        parent::__construct($uuid ?? Uuid::v4()->toRfc4122(), $checkVariant);
    }
}
