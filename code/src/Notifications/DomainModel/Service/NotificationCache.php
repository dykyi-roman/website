<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Profile\User\DomainModel\Enum\UserId;
use Psr\SimpleCache\CacheInterface;

final readonly class NotificationCache
{
    private const string UNREAD_COUNT_KEY = 'user.%s.unread_notifications';

    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function incrementUnreadCount(UserId $userId): void
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());
        $value = $this->cache->get($key);
        $count = is_numeric($value) ? (int) $value : 0;
        $this->cache->set($key, $count + 1);
    }

    public function decrementUnreadCount(UserId $userId): void
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());
        $value = $this->cache->get($key);
        $count = is_numeric($value) ? (int) $value : 0;
        $this->cache->set($key, max(0, $count - 1));
    }

    public function getUnreadCount(UserId $userId): ?int
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());
        $value = $this->cache->get($key);

        if (null === $value) {
            return null;
        }

        return is_numeric($value) ? (int) $value : 0;
    }
}
