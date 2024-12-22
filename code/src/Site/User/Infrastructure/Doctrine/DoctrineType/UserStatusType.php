<?php

declare(strict_types=1);

namespace Site\User\Infrastructure\Doctrine\DoctrineType;

use Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;
use Site\User\DomainModel\Enum\UserStatus;

final class UserStatusType extends IntEnumType
{
    protected const string TYPE_NAME = 'user_status';
    protected const string CLASS_NAME = UserStatus::class;
}
