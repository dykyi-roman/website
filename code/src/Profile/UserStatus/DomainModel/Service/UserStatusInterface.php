<?php

namespace Profile\UserStatus\DomainModel\Service;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Shared\DomainModel\ValueObject\UserId;

interface UserStatusInterface
{
    public function changeStatus(UserUpdateStatus $userUpdateStatus): void;

    public function getStatus(UserId $userId): ?UserUpdateStatus;

    /**
     * @return UserUpdateStatus[]
     */
    public function getAllUserStatuses(): array;
}
