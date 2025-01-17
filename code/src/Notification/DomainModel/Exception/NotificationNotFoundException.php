<?php

declare(strict_types=1);

namespace Notification\DomainModel\Exception;

use Notification\DomainModel\Enum\UserNotificationId;

final class NotificationNotFoundException extends \RuntimeException
{
    public function __construct(
        UserNotificationId $id,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Notification not found by Id: %s', $id->toRfc4122()),
            $code,
            $previous
        );
    }
}
