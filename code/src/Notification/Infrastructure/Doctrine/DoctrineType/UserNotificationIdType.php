<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Doctrine\DoctrineType;

use Notification\DomainModel\Enum\UserNotificationId;
use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class UserNotificationIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'user_notification_id';
    protected const ?string CLASS_NAME = UserNotificationId::class;
}
