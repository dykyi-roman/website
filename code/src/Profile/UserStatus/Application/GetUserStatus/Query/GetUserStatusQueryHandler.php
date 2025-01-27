<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\GetUserStatus\Query;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Profile\UserStatus\DomainModel\Service\UserStatusCacheInterface;

final readonly class GetUserStatusQueryHandler
{
    public function __construct(
        private UserStatusCacheInterface $userStatusCache,
        private UserStatusRepositoryInterface $userStatusRepository,
    ) {
    }

    public function __invoke(GetUserStatusQuery $query): ?UserUpdateStatus
    {
        $status = $this->userStatusCache->getStatus($query->userId);
        if (null === $status) {
            $userStatus = $this->userStatusRepository->findByUserId($query->userId);
            if (null !== $userStatus) {
                return $userStatus->transformToUserUpdateStatus();
            }
        }

        return null;
    }
}