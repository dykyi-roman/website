<?php

namespace Profile\UserStatus\DomainModel\Service;

use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Shared\DomainModel\ValueObject\UserId;

final readonly class UserStatusCache implements UserStatusInterface
{
    private const string CACHE_PREFIX = 'user_status_%s';
    private const string KEYS_CACHE_KEY = 'user_status_keys';

    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private int $isOnlineTtl,
    ) {
    }

    public function changeStatus(UserUpdateStatus $userUpdateStatus): void
    {
        $key = sprintf(self::CACHE_PREFIX, $userUpdateStatus->userId->toRfc4122());
        try {
            // Store the status
            $this->cache->set(
                $key,
                $userUpdateStatus->jsonSerialize(),
                true === $userUpdateStatus->isOnline ? $this->isOnlineTtl : null,
            );

            // Update keys list
            $keys = $this->cache->get(self::KEYS_CACHE_KEY, []);
            if (!is_array($keys)) {
                $keys = [];
            }
            if (!in_array($key, $keys, true)) {
                $keys[] = $key;
                $this->cache->set(self::KEYS_CACHE_KEY, $keys);
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function getStatus(UserId $userId): ?UserUpdateStatus
    {
        $key = sprintf(self::CACHE_PREFIX, $userId->toRfc4122());
        try {
            $data = $this->cache->get($key);
            if (!is_array($data)) {
                return null;
            }

            /* @var array<mixed, mixed> $data */
            return UserUpdateStatus::fromArray($data);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * @return UserUpdateStatus[]
     */
    public function getAllUserStatuses(): array
    {
        $statuses = [];
        try {
            $keys = $this->cache->get(self::KEYS_CACHE_KEY, []);
            if (!is_array($keys) || empty($keys)) {
                return [];
            }

            $values = $this->cache->getMultiple($keys);
            foreach ($values as $value) {
                if (!is_array($value)) {
                    continue;
                }

                /* @var array<mixed, mixed> $value */
                $statuses[] = UserUpdateStatus::fromArray($value);
            }

            // Clean up expired keys
            $this->cleanupExpiredKeys($keys, $values);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $statuses;
    }

    /**
     * Remove expired keys from the keys list.
     *
     * @param array<string>   $keys
     * @param iterable<mixed> $values
     */
    private function cleanupExpiredKeys(array $keys, iterable $values): void
    {
        try {
            $activeKeys = [];
            $index = 0;
            foreach ($values as $value) {
                if (null !== $value) {
                    $activeKeys[] = $keys[$index];
                }
                ++$index;
            }

            if (count($activeKeys) !== count($keys)) {
                $this->cache->set(self::KEYS_CACHE_KEY, $activeKeys);
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
