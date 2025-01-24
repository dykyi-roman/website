<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Type;

use Notifications\DomainModel\Enum\NotificationName;
use Shared\Infrastructure\Persistence\Doctrine\Type\StringEnumType;

final class NotificationNameType extends StringEnumType
{
    protected const ?string TYPE_NAME = 'notification_name';
    protected const ?string CLASS_NAME = NotificationName::class;
}
