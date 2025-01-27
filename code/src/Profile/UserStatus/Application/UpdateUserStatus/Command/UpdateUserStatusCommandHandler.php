<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\UpdateUserStatus\Command;

use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserStatusCommandHandler
{
    public function __construct(
        private UserStatusRepositoryInterface $userStatusRepository,
        private MessageBusInterface $eventBus,
    ) {
    }

    public function __invoke(UpdateUserStatusCommand $command): void
    {
        $this->userStatusRepository->saveOrUpdate(
            ...array_map(static fn (array $item): UserStatus => UserStatus::fromArray($item), $command->items)
        );

        foreach ($command->items as $item) {
            if (false === $item['is_online']) {
                $this->eventBus->dispatch(new UserWentOnlineEvent($item['user_id']));
            }
        }
    }
}
