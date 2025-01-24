<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\UserId;
use Site\Registration\DomainModel\Event\UserChangedPasswordEvent;

#[CoversClass(UserChangedPasswordEvent::class)]
final class UserChangedPasswordEventTest extends TestCase
{
    private UserChangedPasswordEvent $event;

    public function testGetAggregateId(): void
    {
        $expectedUuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = new UserId($expectedUuid);

        $this->event = new UserChangedPasswordEvent($userId);
        $this->assertSame($expectedUuid, $this->event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $userId = new UserId();

        $this->event = new UserChangedPasswordEvent($userId);
        $this->assertSame('user.restored.password', $this->event->getAggregateType());
    }

    public function testGetOccurredOn(): void
    {
        $userId = new UserId();

        $this->event = new UserChangedPasswordEvent($userId);
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }

    public function testGetPayload(): void
    {
        $userId = new UserId();

        $this->event = new UserChangedPasswordEvent($userId);
        $this->assertSame([], $this->event->getPayload());
    }

    public function testGetVersion(): void
    {
        $userId = new UserId();
        $this->event = new UserChangedPasswordEvent($userId);
        $this->assertSame(1, $this->event->getVersion());
    }
}
