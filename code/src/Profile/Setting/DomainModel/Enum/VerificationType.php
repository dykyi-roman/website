<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\Enum;

enum VerificationType: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';

    public function isEmail(): bool
    {
        return self::EMAIL === $this;
    }

    public function isPhone(): bool
    {
        return self::PHONE === $this;
    }
}
