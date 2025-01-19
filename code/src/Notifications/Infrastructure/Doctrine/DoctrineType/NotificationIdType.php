<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Doctrine\DoctrineType;

use Notifications\DomainModel\Enum\NotificationId;
use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;

final class NotificationIdType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'notification_id';
    protected const ?string CLASS_NAME = NotificationId::class;
}
