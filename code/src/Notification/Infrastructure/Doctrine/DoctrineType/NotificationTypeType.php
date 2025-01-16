<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Doctrine\DoctrineType;

use Notification\DomainModel\Enum\NotificationType;
use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;

final class NotificationTypeType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'notification_type';
    protected const ?string CLASS_NAME = NotificationType::class;
}
