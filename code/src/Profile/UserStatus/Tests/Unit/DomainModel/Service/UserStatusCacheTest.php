<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Service\UserStatusCache;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UserStatusCache::class)]
final class UserStatusCacheTest extends TestCase
{
    private const int ONLINE_TTL = 300;
    private const string VALID_UUID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CACHE_PREFIX = 'user_status_%s';
    private const string KEYS_CACHE_KEY = 'user_status_keys';

    /** @var CacheInterface&MockObject */
    private MockObject $cache;

    /** @var LoggerInterface&MockObject */
    private MockObject $logger;

    private UserStatusCache $userStatusCache;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userStatusCache = new UserStatusCache($this->cache, $this->logger, self::ONLINE_TTL);
    }

    public function testChangeStatusForOnlineUser(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $status = new UserUpdateStatus($userId, true, new \DateTimeImmutable());
        $cacheKey = sprintf(self::CACHE_PREFIX, $userId->toRfc4122());

        $setInvocation = 0;
        $this->cache->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value, $ttl = null) use ($cacheKey, $status, &$setInvocation) {
                if (0 === $setInvocation) {
                    $this->assertEquals($cacheKey, $key);
                    $this->assertEquals($status->jsonSerialize(), $value);
                    $this->assertEquals(self::ONLINE_TTL, $ttl);
                } else {
                    $this->assertEquals(self::KEYS_CACHE_KEY, $key);
                    $this->assertEquals([$cacheKey], $value);
                }
                ++$setInvocation;

                return true;
            });

        $this->cache->expects($this->once())
            ->method('get')
            ->with(self::KEYS_CACHE_KEY)
            ->willReturn([]);

        $this->userStatusCache->changeStatus($status);

        // Add assertion to mark test as not risky
        $this->assertTrue(true);
    }

    public function testChangeStatusForOfflineUser(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $status = new UserUpdateStatus($userId, false, new \DateTimeImmutable());
        $cacheKey = sprintf(self::CACHE_PREFIX, $userId->toRfc4122());

        $setInvocation = 0;
        $this->cache->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($key, $value, $ttl = null) use ($cacheKey, $status, &$setInvocation) {
                if (0 === $setInvocation) {
                    $this->assertEquals($cacheKey, $key);
                    $this->assertEquals($status->jsonSerialize(), $value);
                    $this->assertNull($ttl);
                } else {
                    $this->assertEquals(self::KEYS_CACHE_KEY, $key);
                    $this->assertEquals([$cacheKey], $value);
                }
                ++$setInvocation;

                return true;
            });

        $this->cache->method('get')
            ->with(self::KEYS_CACHE_KEY)
            ->willReturn([]);

        $this->userStatusCache->changeStatus($status);

        // Add assertion to mark test as not risky
        $this->assertTrue(true);
    }

    public function testChangeStatusHandlesCacheException(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $status = new UserUpdateStatus($userId, true, new \DateTimeImmutable());

        $exception = new class extends \Exception implements InvalidArgumentException {};

        $this->cache->method('set')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->userStatusCache->changeStatus($status);
    }

    public function testGetStatusReturnsNullForNonexistentUser(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $cacheKey = sprintf(self::CACHE_PREFIX, $userId->toRfc4122());

        $this->cache->method('get')
            ->with($cacheKey)
            ->willReturn(null);

        $this->assertNull($this->userStatusCache->getStatus($userId));
    }

    public function testGetStatusReturnsUserStatus(): void
    {
        $userId = new UserId(self::VALID_UUID);
        $cacheKey = sprintf(self::CACHE_PREFIX, $userId->toRfc4122());
        $lastOnlineAt = new \DateTimeImmutable();

        $cachedData = [
            'user_id' => self::VALID_UUID,
            'is_online' => true,
            'last_online_at' => $lastOnlineAt->format('c'),
        ];

        $this->cache->method('get')
            ->with($cacheKey)
            ->willReturn($cachedData);

        $status = $this->userStatusCache->getStatus($userId);

        $this->assertInstanceOf(UserUpdateStatus::class, $status);
        $this->assertEquals($userId->toRfc4122(), $status->userId->toRfc4122());
        $this->assertTrue($status->isOnline);
        $this->assertEquals($lastOnlineAt->format('c'), $status->lastOnlineAt->format('c'));
    }

    public function testGetAllUserStatusesReturnsEmptyArrayWhenNoKeys(): void
    {
        $this->cache->method('get')
            ->with(self::KEYS_CACHE_KEY)
            ->willReturn([]);

        $this->assertEquals([], $this->userStatusCache->getAllUserStatuses());
    }

    public function testGetAllUserStatusesReturnsAllValidStatuses(): void
    {
        $userId1 = new UserId(self::VALID_UUID);
        $userId2 = new UserId('650e8400-e29b-41d4-a716-446655440001');

        $key1 = sprintf(self::CACHE_PREFIX, $userId1->toRfc4122());
        $key2 = sprintf(self::CACHE_PREFIX, $userId2->toRfc4122());

        $status1 = [
            'user_id' => $userId1->toRfc4122(),
            'is_online' => true,
            'last_online_at' => '2024-01-01T12:00:00+00:00',
        ];

        $status2 = [
            'user_id' => $userId2->toRfc4122(),
            'is_online' => false,
            'last_online_at' => '2024-01-01T13:00:00+00:00',
        ];

        $this->cache->method('get')
            ->with(self::KEYS_CACHE_KEY)
            ->willReturn([$key1, $key2]);

        $this->cache->method('getMultiple')
            ->with([$key1, $key2])
            ->willReturn([$status1, $status2]);

        $statuses = $this->userStatusCache->getAllUserStatuses();

        $this->assertCount(2, $statuses);
        $this->assertEquals($userId1->toRfc4122(), $statuses[0]->userId->toRfc4122());
        $this->assertEquals($userId2->toRfc4122(), $statuses[1]->userId->toRfc4122());
    }

    public function testGetAllUserStatusesHandlesExpiredKeys(): void
    {
        $userId1 = new UserId(self::VALID_UUID);
        $userId2 = new UserId('650e8400-e29b-41d4-a716-446655440001');

        $key1 = sprintf(self::CACHE_PREFIX, $userId1->toRfc4122());
        $key2 = sprintf(self::CACHE_PREFIX, $userId2->toRfc4122());

        $status1 = [
            'user_id' => $userId1->toRfc4122(),
            'is_online' => true,
            'last_online_at' => '2024-01-01T12:00:00+00:00',
        ];

        $this->cache->method('get')
            ->with(self::KEYS_CACHE_KEY)
            ->willReturn([$key1, $key2]);

        // Second key is expired (null value)
        $this->cache->method('getMultiple')
            ->with([$key1, $key2])
            ->willReturn([$status1, null]);

        // Expect cleanup of expired keys
        $this->cache->expects($this->once())
            ->method('set')
            ->with(self::KEYS_CACHE_KEY, [$key1]);

        $statuses = $this->userStatusCache->getAllUserStatuses();

        $this->assertCount(1, $statuses);
        $this->assertEquals($userId1->toRfc4122(), $statuses[0]->userId->toRfc4122());
    }

    public function testGetAllUserStatusesHandlesCacheException(): void
    {
        $exception = new class extends \Exception implements InvalidArgumentException {};

        $this->cache->method('get')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());

        $this->assertEquals([], $this->userStatusCache->getAllUserStatuses());
    }
}
