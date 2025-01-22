<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Type;

use Notifications\DomainModel\Enum\NotificationType;
use Shared\Infrastructure\Doctrine\DoctrineType\StringEnumType;

final class NotificationTypeType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'notification_type';
    protected const ?string CLASS_NAME = NotificationType::class;
}
