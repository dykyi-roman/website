<?php

declare(strict_types=1);

namespace Profile\User\Infrastructure\Persistence\Doctrine\Type;

use Profile\User\DomainModel\Enum\UserStatus;
use Shared\Infrastructure\Persistence\Doctrine\Type\IntEnumType;

final class UserStatusType extends IntEnumType
{
    protected const ?string TYPE_NAME = 'user_status';
    protected const ?string CLASS_NAME = UserStatus::class;
}
