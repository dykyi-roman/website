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
        $this->userStatusRepository->saveOrUpdate(
            ...array_map(static fn(array $item): UserStatus => UserStatus::fromArray($item), $command->items)
        );
    }
}