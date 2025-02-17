<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Type;

use Notifications\DomainModel\ValueObject\UserNotificationId;
use Shared\Infrastructure\Persistence\Doctrine\Type\UuidType;

final class UserNotificationIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'user_notification_id';
    protected const ?string CLASS_NAME = UserNotificationId::class;
}
