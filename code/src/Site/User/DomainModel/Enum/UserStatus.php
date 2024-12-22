<?php

declare(strict_types=1);

namespace Site\User\DomainModel\Enum;

enum UserStatus: int
{
    case ACTIVATED = 1;
    case DEACTIVATED = 0;

    public function isActive(): bool
    {
        return self::ACTIVATED === $this;
    }
}
