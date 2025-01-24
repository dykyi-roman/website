<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Exception;

use Notifications\DomainModel\ValueObject\UserNotificationId;

final class UserNotificationNotFoundException extends \RuntimeException
{
    public function __construct(
        UserNotificationId $id,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('User Notification not found by Id: %s', $id->toRfc4122()),
            $code,
            $previous
        );
    }
}
