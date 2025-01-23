<?php

declare(strict_types=1);

namespace Profile\User\Infrastructure\Persistence\Doctrine\Type;

use Profile\User\DomainModel\Enum\UserId;
use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;

final class UserIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'user_id';
    protected const ?string CLASS_NAME = UserId::class;
}
