<?php

declare(strict_types=1);

namespace EventStorage\Infrastructure\Persistence\Doctrine\Type;

use EventStorage\DomainModel\Enum\EventId;
use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class EventIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'event_id';
    protected const ?string CLASS_NAME = EventId::class;
}
