<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Exception;

use Notifications\DomainModel\Enum\NotificationId;

final class NotificationNotFoundException extends \RuntimeException
{
    public function __construct(
        NotificationId $id,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf('Notification not found by Id: %s', $id->value),
            $code,
            $previous
        );
    }
}
