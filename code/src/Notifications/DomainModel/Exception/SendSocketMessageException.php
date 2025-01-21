<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Exception;

final class SendSocketMessageException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
