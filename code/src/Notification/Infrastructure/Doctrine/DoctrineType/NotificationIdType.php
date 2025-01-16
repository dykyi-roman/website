<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Doctrine\DoctrineType;

use Notification\DomainModel\Enum\NotificationId;
use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class NotificationIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'notification_id';
    protected const ?string CLASS_NAME = NotificationId::class;
}
