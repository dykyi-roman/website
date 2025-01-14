<?php

declare(strict_types=1);

namespace Shared\DomainModel\Services;

interface MessageBusInterface
{
    /**
     * @throws \Throwable
     */
    public function dispatch(object $message): mixed;
}
