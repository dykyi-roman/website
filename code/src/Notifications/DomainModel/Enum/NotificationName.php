<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Enum;

enum NotificationName: string
{
    case HAPPY_BIRTHDAY = 'HAPPY_BIRTHDAY';
    case HAPPY_NEW_YEAR = 'HAPPY_NEW_YEAR';
    case PASS_VERIFICATION = 'PASS_VERIFICATION';
}
