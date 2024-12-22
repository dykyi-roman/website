<?php

declare(strict_types=1);

namespace Site\User\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\UuidType;
use Site\User\DomainModel\Enum\UserId;

final class UserIdType extends UuidType
{
    protected const string TYPE_NAME = 'user_id';
    protected const string CLASS_NAME = UserId::class;
}
