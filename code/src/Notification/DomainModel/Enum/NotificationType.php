<?php

declare(strict_types=1);

namespace Notification\DomainModel\Enum;

enum NotificationType: string
{
    case PERSONAL = 'personal';
    case SYSTEM = 'system';
    case INFORMATION = 'information';
    case WARNING = 'warning';
    case ERROR = 'error';
}
