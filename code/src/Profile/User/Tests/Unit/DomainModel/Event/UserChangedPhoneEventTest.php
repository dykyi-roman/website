<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Event\UserChangedPhoneEvent;

#[CoversClass(UserChangedPhoneEvent::class)]
final class UserChangedPhoneEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $oldPhone = '+1234567890';
        $newPhone = '+0987654321';

        $event = new UserChangedPhoneEvent($userId, $oldPhone, $newPhone);

        self::assertSame($userId, $event->id);
        self::assertSame($oldPhone, $event->oldPhone);
        self::assertSame($newPhone, $event->newPhone);
    }

    public function testEventCreationWithNullOldPhone(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $newPhone = '+0987654321';

        $event = new UserChangedPhoneEvent($userId, null, $newPhone);

        self::assertSame($userId, $event->id);
        self::assertNull($event->oldPhone);
        self::assertSame($newPhone, $event->newPhone);
    }

    public function testGetAggregateId(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $event = new UserChangedPhoneEvent(
            $userId,
            '+1234567890',
            '+0987654321'
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $event = new UserChangedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            '+1234567890',
            '+0987654321'
        );

        self::assertSame('user.change.phone', $event->getAggregateType());
    }

    public function testGetPayload(): void
    {
        $event = new UserChangedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            '+1234567890',
            '+0987654321'
        );

        $expectedPayload = [
            'from' => '+1234567890',
            'to' => '+0987654321',
        ];

        self::assertSame($expectedPayload, $event->getPayload());
    }

    public function testGetPayloadWithNullOldPhone(): void
    {
        $event = new UserChangedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            null,
            '+0987654321'
        );

        $expectedPayload = [
            'from' => '',
            'to' => '+0987654321',
        ];

        self::assertSame($expectedPayload, $event->getPayload());
    }

    public function testGetVersion(): void
    {
        $event = new UserChangedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            '+1234567890',
            '+0987654321'
        );

        self::assertSame(1, $event->getVersion());
    }

    public function testGetOccurredOn(): void
    {
        $event = new UserChangedPhoneEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            '+1234567890',
            '+0987654321'
        );

        self::assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }
}
