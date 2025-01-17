<?php

declare(strict_types=1);

namespace Notification\DomainModel\Enum;

enum NotificationId: string
{
    case HAPPY_NEW_YEAR = 'HAPPY_NEW_YEAR';
    case HAPPY_BIRTHDAY = 'HAPPY_BIRTHDAY';
    case PASS_VERIFICATION = 'pass_verification';
}