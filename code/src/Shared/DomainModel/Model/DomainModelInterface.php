<?php

declare(strict_types=1);

namespace Shared\DomainModel\Model;

use Shared\DomainModel\Event\DomainEventInterface;

interface DomainModelInterface
{
    public function raise(DomainEventInterface $event): void;

    /**
     * @return DomainEventInterface[]
     */
    public function releaseEvents(): array;
}
