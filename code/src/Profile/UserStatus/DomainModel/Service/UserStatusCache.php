<?php

namespace Profile\UserStatus\DomainModel\Service;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Event\UserWentOfflineEvent;
use Profile\UserStatus\DomainModel\Event\UserWentOnlineEvent;
use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;

final readonly class UserStatusCache implements UserStatusCacheInterface
{
    public function __construct(
        private CacheInterface $cache,
        private MessageBusInterface $eventBus,
        private int $isOnlineTtl,
    ) {
    }

    public function changeStatus(UserUpdateStatus $userUpdateStatus): void
    {
        $key = sprintf("user:status:%s", $userUpdateStatus->userId->toRfc4122());
        $this->cache->set(
            $key,
            $userUpdateStatus->jsonSerialize(),
            true === $userUpdateStatus->isOnline ? $this->isOnlineTtl : null,
        );

        if ($userUpdateStatus->isOnline) {
            $this->eventBus->dispatch(new UserWentOnlineEvent($userUpdateStatus->userId));
        } else {
            $this->eventBus->dispatch(new UserWentOfflineEvent($userUpdateStatus->userId));
        }
    }

    public function getStatus(UserId $userId): ?UserUpdateStatus
    {
        $key = sprintf("user:status:%s", $userId->toRfc4122());
        $data = $this->cache->get($key);
        if (is_array($data)) {
            return UserUpdateStatus::fromArray($data);
        }

        $this->eventBus->dispatch(new UserWentOfflineEvent($userId));

        return null;
    }
}