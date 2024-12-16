<?php

declare(strict_types=1);

namespace App\Shared\DomainModel\Services;

interface MessageBusInterface
{
    /**
     * Dispatches the given message.
     *
     * @template T
     * @param T $message The message to dispatch
     * @return T The handler returned value
     */
    public function dispatch(object $message): mixed;
}
