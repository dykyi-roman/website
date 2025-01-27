<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\UpdateUserStatus\Command;

use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;
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
            ...array_map(
                /** @param mixed $item */
                static function (mixed $item): UserStatus {
                    if (!is_array($item)) {
                        throw new \InvalidArgumentException('Item must be an array');
                    }
                    /** @var array<string, mixed> $item */
                    return UserStatus::fromArray($item);
                },
                $command->items
            )
        );

        foreach ($command->items as $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException('Item must be an array');
            }

            if (!isset($item['is_online'], $item['user_id'])) {
                throw new \InvalidArgumentException('Item must contain is_online and user_id keys');
            }

            if (false === $item['is_online']) {
                $this->eventBus->dispatch(new UserWentOnlineEvent(new UserId($item['user_id'])));
            }
        }
    }
}
