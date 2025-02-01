<?php

declare(strict_types=1);

namespace Profile\UserStatus\Tests\Unit\DomainModel\Dto;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UserUpdateStatus::class)]
final class UserUpdateStatusTest extends TestCase
{
    private string $validUuid;

    protected function setUp(): void
    {
        $this->validUuid = '550e8400-e29b-41d4-a716-446655440000';
    }

    public function testConstructorCreatesValidInstance(): void
    {
        $userId = new UserId($this->validUuid);
        $isOnline = true;
        $lastOnlineAt = new \DateTimeImmutable();

        $status = new UserUpdateStatus($userId, $isOnline, $lastOnlineAt);

        $this->assertSame($userId, $status->userId);
        $this->assertSame($isOnline, $status->isOnline);
        $this->assertSame($lastOnlineAt, $status->lastOnlineAt);
    }

    public function testCreateOnlineCreatesOnlineStatus(): void
    {
        $userId = new UserId($this->validUuid);

        $status = UserUpdateStatus::createOnline($userId);

        $this->assertSame($userId, $status->userId);
        $this->assertTrue($status->isOnline);
        $this->assertInstanceOf(\DateTimeImmutable::class, $status->lastOnlineAt);
        // Ensure the creation time is recent (within last second)
        $this->assertLessThanOrEqual(1, abs(time() - $status->lastOnlineAt->getTimestamp()));
    }

    public function testFromArrayWithValidData(): void
    {
        $data = [
            'user_id' => $this->validUuid,
            'is_online' => true,
            'last_online_at' => '2024-01-01T12:00:00+00:00',
        ];

        $status = UserUpdateStatus::fromArray($data);

        $this->assertEquals($this->validUuid, $status->userId->toRfc4122());
        $this->assertTrue($status->isOnline);
        $this->assertEquals('2024-01-01T12:00:00+00:00', $status->lastOnlineAt->format('c'));
    }

    public function testFromArrayWithInvalidUserId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('user_id must be a string');

        UserUpdateStatus::fromArray([
            'user_id' => 123, // Invalid type
            'is_online' => true,
            'last_online_at' => '2024-01-01T12:00:00+00:00',
        ]);
    }

    public function testFromArrayWithInvalidLastOnlineAt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('last_online_at must be a string');

        UserUpdateStatus::fromArray([
            'user_id' => $this->validUuid,
            'is_online' => true,
            'last_online_at' => 123, // Invalid type
        ]);
    }

    public function testFromArrayWithInvalidDateFormat(): void
    {
        $this->expectException(\Exception::class);

        UserUpdateStatus::fromArray([
            'user_id' => $this->validUuid,
            'is_online' => true,
            'last_online_at' => 'invalid-date',
        ]);
    }

    public function testJsonSerialize(): void
    {
        $userId = new UserId($this->validUuid);
        $lastOnlineAt = new \DateTimeImmutable('2024-01-01T12:00:00+00:00');
        $status = new UserUpdateStatus($userId, true, $lastOnlineAt);

        $expected = [
            'user_id' => $this->validUuid,
            'is_online' => true,
            'last_online_at' => '2024-01-01T12:00:00+00:00',
        ];

        $this->assertEquals($expected, $status->jsonSerialize());

        // Test JSON encoding
        $this->assertJsonStringEqualsJsonString(
            json_encode($expected),
            json_encode($status)
        );
    }
}
