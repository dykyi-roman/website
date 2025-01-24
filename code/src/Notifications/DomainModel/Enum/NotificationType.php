<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Enum;

enum NotificationType: string
{
    case PERSONAL = 'PERSONAL';
    case SYSTEM = 'SYSTEM';
    case INFORMATION = 'INFORMATION';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
}
