<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Event\UserVerifiedPhoneEvent;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UserVerifiedPhoneEvent::class)]
final class UserVerifiedPhoneEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $event = new UserVerifiedPhoneEvent($userId);

        self::assertSame($userId, $event->id);
    }

    public function testGetAggregateId(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $event = new UserVerifiedPhoneEvent($userId);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $event = new UserVerifiedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000')
        );

        self::assertSame('user.change.phone', $event->getAggregateType());
    }

    public function testGetPayload(): void
    {
        $event = new UserVerifiedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000')
        );

        self::assertSame([], $event->getPayload());
    }

    public function testGetVersion(): void
    {
        $event = new UserVerifiedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000')
        );

        self::assertSame(1, $event->getVersion());
    }

    public function testGetOccurredOn(): void
    {
        $event = new UserVerifiedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000')
        );

        self::assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }
}
