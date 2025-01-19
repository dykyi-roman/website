<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Enum;

enum NotificationId: string
{
    case HAPPY_NEW_YEAR = 'HAPPY_NEW_YEAR';
    case HAPPY_BIRTHDAY = 'HAPPY_BIRTHDAY';
    case PASS_VERIFICATION = 'PASS_VERIFICATION';
}
