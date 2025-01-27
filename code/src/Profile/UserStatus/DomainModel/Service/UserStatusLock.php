<?php

declare(strict_types=1);

namespace Profile\UserStatus\DomainModel\Service;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\SimpleCache\CacheInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class UserStatusLock
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private \Redis $redis,
    ) {
    }

    public function updateUserStatus(UserId $userId, bool $isActive): void
    {
        $store = new RedisStore($this->redis);
        $lockFactory = new LockFactory($store);
        $lock = $lockFactory->createLock("user_status_{$userId}");

        try {
            $lock->acquire(true);
            $user = $this->userRepository->findById($userId);
            if ($isActive) {
                $user->markOnline();
            } else {
                $user->markOffline();
            }

            $this->userRepository->save($user);
        } finally {
            $lock->release();
        }
    }

    public function getUserStatus(UserId $userId): ?array
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return null;
        }

        return [
            'is_online' => $user->isOnline(),
            'last_online_at' => $user->getLastOnlineAt(),
            'online_sessions' => $user->getOnlineSessionCount()
        ];
    }
}