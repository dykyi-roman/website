<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\DomainModel\Service;

use Notifications\DomainModel\Service\NotificationCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Enum\UserId;
use Psr\SimpleCache\CacheInterface;

#[CoversClass(NotificationCache::class)]
final class NotificationCacheTest extends TestCase
{
    private CacheInterface&MockObject $cache;
    private NotificationCache $notificationCache;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->notificationCache = new NotificationCache($this->cache);
        $this->userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
    }

    public function testIncrementUnreadCount(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        // When cache is empty
        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($key, 1);

        $this->notificationCache->incrementUnreadCount($this->userId);
    }

    public function testIncrementUnreadCountWithExistingValue(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(5);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($key, 6);

        $this->notificationCache->incrementUnreadCount($this->userId);
    }

    public function testDecrementUnreadCount(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        // When cache has value
        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(5);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($key, 4);

        $this->notificationCache->decrementUnreadCount($this->userId);
    }

    public function testDecrementUnreadCountWithZeroValue(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(0);

        $this->cache->expects($this->once())
            ->method('set')
            ->with($key, 0);

        $this->notificationCache->decrementUnreadCount($this->userId);
    }

    public function testResetUnreadCount(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('set')
            ->with($key, 0);

        $this->notificationCache->resetUnreadCount($this->userId);
    }

    public function testGetUnreadCount(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(5);

        $result = $this->notificationCache->getUnreadCount($this->userId);
        $this->assertSame(5, $result);
    }

    public function testGetUnreadCountReturnsNullWhenNoValue(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn(null);

        $result = $this->notificationCache->getUnreadCount($this->userId);
        $this->assertNull($result);
    }

    public function testGetUnreadCountHandlesNonNumericValue(): void
    {
        $key = 'user.550e8400-e29b-41d4-a716-446655440000.unread_notifications';

        $this->cache->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn('invalid');

        $result = $this->notificationCache->getUnreadCount($this->userId);
        $this->assertSame(0, $result);
    }
}
