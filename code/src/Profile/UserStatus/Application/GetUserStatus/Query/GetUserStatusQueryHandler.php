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
        $status = $this->userStatus->getStatus($query->userId);
        if (null === $status) {
            $userStatus = $this->userStatusRepository->findByUserId($query->userId);
            if (null !== $userStatus) {
                return new UserUpdateStatus(
                    $userStatus->getUserId(),
                    $userStatus->isOnline(),
                    $userStatus->getLastOnlineAt(),
                );
            }
        }

        return null;
    }
}
