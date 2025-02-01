<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\GetUserStatus\Query;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Profile\UserStatus\DomainModel\Service\UserStatusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserStatusQueryHandler
{
    public function __construct(
        private UserStatusInterface $userStatus,
        private UserStatusRepositoryInterface $userStatusRepository,
    ) {
    }

    public function __invoke(GetUserStatusQuery $query): ?UserUpdateStatus
    {
        // Try to get status from cache
        if ($status = $this->userStatus->getStatus($query->userId)) {
            return $status;
        }

        // If not in cache, try to get from repository
        $userStatus = $this->userStatusRepository->findByUserId($query->userId);
        if ($userStatus) {
            return new UserUpdateStatus(
                $userStatus->getUserId(),
                $userStatus->isOnline(),
                $userStatus->getLastOnlineAt(),
            );
        }

        return null;
    }
}
