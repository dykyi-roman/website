<?php

declare(strict_types=1);

namespace Profile\User\Infrastructure\Doctrine\DoctrineType;

use Profile\User\DomainModel\Enum\UserStatus;
use Shared\Infrastructure\Doctrine\DoctrineType\IntEnumType;

final class UserStatusType extends IntEnumType
{
    protected const ?string TYPE_NAME = 'user_status';
    protected const ?string CLASS_NAME = UserStatus::class;
}
