<?php

declare(strict_types=1);

namespace Site\Profile\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;
use Site\Profile\DomainModel\Enum\SettingId;
use Site\User\DomainModel\Enum\UserId;

final class SettingIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'setting_id';
    protected const ?string CLASS_NAME = SettingId::class;
}
