<?php

declare(strict_types=1);

namespace Profile\Setting\Infrastructure\Persistence\Doctrine\Type;

use Profile\Setting\DomainModel\ValueObject\SettingId;
use Shared\Infrastructure\Persistence\Doctrine\Type\UuidType;

final class SettingIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'setting_id';
    protected const ?string CLASS_NAME = SettingId::class;
}
