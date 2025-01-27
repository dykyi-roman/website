<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Repository;

use Profile\UserStatus\DomainModel\Model\UserStatus;
use Shared\DomainModel\ValueObject\UserId;

interface UserStatusRepositoryInterface
{
    public function save(UserStatus $userStatus): void;

    public function findByUserId(UserId $userId): ?UserStatus;
}