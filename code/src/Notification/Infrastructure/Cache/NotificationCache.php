<?php

declare(strict_types=1);

namespace Notification\Infrastructure\Cache;

use Profile\User\DomainModel\Enum\UserId;
use Psr\SimpleCache\CacheInterface;

final readonly class NotificationCache
{
    private const string UNREAD_COUNT_KEY = 'user:%d:unread_notifications';

    public function __construct(
        private CacheInterface $cache,
    ) {}

    public function incrementUnreadCount(UserId $userId): void
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());
        $this->cache->increment($key);
    }

    public function decrementUnreadCount(UserId $userId): void
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());
        $this->cache->decrement($key);
    }

    public function getUnreadCount(UserId $userId): int|null
    {
        $key = sprintf(self::UNREAD_COUNT_KEY, $userId->toRfc4122());

        return $this->cache->get($key);
    }
}
