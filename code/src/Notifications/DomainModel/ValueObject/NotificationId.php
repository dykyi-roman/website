<?php

declare(strict_types=1);

namespace Notifications\DomainModel\ValueObject;

use Symfony\Component\Uid\Uuid;

final class NotificationId extends Uuid
{
    public const HAPPY_NEW_YEAR = '123e4567-e89b-12d3-a456-426614174000';

    public function __construct(?string $uuid = null, bool $checkVariant = false)
    {
        parent::__construct($uuid ?? Uuid::v4()->toRfc4122(), $checkVariant);
    }
}
