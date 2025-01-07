<?php

declare(strict_types=1);

namespace EventStorage\DomainModel\Repository;

use EventStorage\DomainModel\Model\Event;

interface EventRepositoryInterface
{
    public function save(Event $event): void;
}
