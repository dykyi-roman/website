<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\Exception;

final class DuplicateEventException extends \DomainException
{
    public function __construct(int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct('Duplicate event detected', $code, $previous);
    }
}
