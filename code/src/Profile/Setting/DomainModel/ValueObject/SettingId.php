<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\ValueObject;

use Symfony\Component\Uid\Uuid;

final class SettingId extends Uuid
{
    public function __construct(?string $uuid = null, bool $checkVariant = false)
    {
        parent::__construct($uuid ?? Uuid::v4()->toRfc4122(), $checkVariant);
    }
}
