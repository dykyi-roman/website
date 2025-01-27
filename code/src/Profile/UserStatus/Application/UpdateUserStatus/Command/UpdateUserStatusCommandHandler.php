<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\UpdateUserStatus\Command;

use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserStatusCommandHandler
{
    public function __construct(
        private UserStatusRepositoryInterface $userStatusRepository,
    ) {
    }

    public function __invoke(UpdateUserStatusCommand $command): void
    {
        $statuses = [];
        foreach ($command->items as $item) {
            $statuses[] = UserStatus::fromArray($item);
        }

        $this->userStatusRepository->save(...$statuses);
    }
}