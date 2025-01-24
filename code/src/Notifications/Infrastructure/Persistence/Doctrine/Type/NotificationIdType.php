<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Type;

use Notifications\DomainModel\ValueObject\NotificationId;
use Shared\Infrastructure\Persistence\Doctrine\Type\UuidType;

final class NotificationIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'notification_id';
    protected const ?string CLASS_NAME = NotificationId::class;
}
