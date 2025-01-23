<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\DomainModel\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Enum\UserId;

#[CoversClass(UserId::class)]
final class UserIdTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuid);

        $this->assertSame($uuid, $userId->toRfc4122());
        $this->assertSame($uuid, (string) $userId);
    }

    public function testCreateNew(): void
    {
        $userId = new UserId();
        
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $userId->toRfc4122()
        );
    }

    public function testEquality(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1 = UserId::fromString($uuid);
        $id2 = UserId::fromString($uuid);
        $id3 = new UserId();

        $this->assertTrue($id1->equals($id2));
        $this->assertTrue($id2->equals($id1));
        $this->assertFalse($id1->equals($id3));
        $this->assertFalse($id3->equals($id1));
    }

    public function testInvalidUuid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UserId::fromString('invalid-uuid');
    }
}
