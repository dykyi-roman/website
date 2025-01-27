<?php

declare(strict_types=1);

namespace Profile\UserStatus\Infrastructure\Persistence\Doctrine\Type;

use Profile\UserStatus\DomainModel\ValueObject\UserStatusId;
use Shared\Infrastructure\Persistence\Doctrine\Type\UuidType;

final class UserStatusIdType extends UuidType
{
    protected const ?string TYPE_NAME = 'user_status_id';
    protected const ?string CLASS_NAME = UserStatusId::class;
}
