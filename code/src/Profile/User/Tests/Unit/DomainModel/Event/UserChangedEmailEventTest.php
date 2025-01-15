<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Event\UserChangedEmailEvent;
use Shared\DomainModel\ValueObject\Email;

#[CoversClass(UserChangedEmailEvent::class)]
final class UserChangedEmailEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $oldEmail = Email::fromString('old@example.com');
        $newEmail = Email::fromString('new@example.com');

        $event = new UserChangedEmailEvent($userId, $oldEmail, $newEmail);

        self::assertSame($userId, $event->id);
        self::assertSame($oldEmail, $event->oldEmail);
        self::assertSame($newEmail, $event->newEmail);
    }

    public function testGetAggregateId(): void
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $event = new UserChangedEmailEvent(
            $userId,
            Email::fromString('old@example.com'),
            Email::fromString('new@example.com')
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $event = new UserChangedEmailEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            Email::fromString('old@example.com'),
            Email::fromString('new@example.com')
        );

        self::assertSame('user.change.email', $event->getAggregateType());
    }

    public function testGetPayload(): void
    {
        $event = new UserChangedEmailEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            Email::fromString('old@example.com'),
            Email::fromString('new@example.com')
        );

        $expectedPayload = [
            'from' => 'old@example.com',
            'to' => 'new@example.com',
        ];

        self::assertSame($expectedPayload, $event->getPayload());
    }

    public function testGetVersion(): void
    {
        $event = new UserChangedEmailEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            Email::fromString('old@example.com'),
            Email::fromString('new@example.com')
        );

        self::assertSame(1, $event->getVersion());
    }

    public function testGetOccurredOn(): void
    {
        $event = new UserChangedEmailEvent(
            UserId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            Email::fromString('old@example.com'),
            Email::fromString('new@example.com')
        );

        self::assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }
}
