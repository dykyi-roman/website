<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Enum;

enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;

    public function isActive(): bool
    {
        return self::ACTIVE === $this;
    }
}
