<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\GetUserStatus\Command;

use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;

final readonly class UpdateUserStatusCommandHandler
{
    public function __construct(
        private UserStatusRepositoryInterface $userStatusRepository,
    ) {
    }

    public function __invoke(UpdateUserStatusCommand $command): void
    {
        $this->userStatusRepository->save(
            new UserStatus(
                $command->userId,
                $command->isOnline,
                $command->lastOnlineAt,
            ),
        );
    }
}