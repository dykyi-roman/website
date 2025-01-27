<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\GetUserStatus\Service;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;

final readonly class UserStatusService
{
    public function __construct(
        private UserStatusInterface $userStatus,
    ) {
    }

    /**
     * @return UserUpdateStatus[]
     */
    public function getAllUserStatuses(): array
    {
        return $this->userStatus->getAllUserStatuses();
    }
}
