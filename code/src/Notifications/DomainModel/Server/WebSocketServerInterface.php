<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Server;

interface WebSocketServerInterface
{
    public function run(): void;
}
