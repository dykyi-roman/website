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
            $this->cache->set(
                $key,
                $userUpdateStatus->jsonSerialize(),
                true === $userUpdateStatus->isOnline ? $this->isOnlineTtl : null,
            );

            $this->getAllUserStatuses();
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
            $items = $this->cache->getMultiple([sprintf(self::CACHE_PREFIX, '') . '*']);
            $itemsArray = is_array($items) ? $items : iterator_to_array($items);

            foreach ($itemsArray as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }
                /* @var array<mixed, mixed> $value */
                $statuses[] = UserUpdateStatus::fromArray($value);
            }
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $statuses;
    }
}
