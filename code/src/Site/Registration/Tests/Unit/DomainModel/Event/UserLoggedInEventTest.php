<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\UserId;
use Site\Registration\DomainModel\Event\UserLoggedInEvent;

#[CoversClass(UserLoggedInEvent::class)]
final class UserLoggedInEventTest extends TestCase
{
    private UserLoggedInEvent $event;

    public function testGetAggregateId(): void
    {
        $expectedUuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = new UserId($expectedUuid);
        $email = Email::fromString('test@example.com');

        $this->event = new UserLoggedInEvent($userId, $email, 'customer');
        $this->assertSame($expectedUuid, $this->event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $userId = new UserId();
        $email = Email::fromString('test@example.com');

        $this->event = new UserLoggedInEvent($userId, $email, 'customer');
        $this->assertSame('user.logged.customer', $this->event->getAggregateType());
    }

    public function testGetOccurredOn(): void
    {
        $userId = new UserId();
        $email = Email::fromString('test@example.com');

        $this->event = new UserLoggedInEvent($userId, $email, 'customer');
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->event->getOccurredOn());
    }

    public function testGetPayload(): void
    {
        $userId = new UserId();
        $email = Email::fromString('test@example.com');

        $this->event = new UserLoggedInEvent($userId, $email, 'customer');
        $expectedPayload = ['email' => 'test@example.com'];
        $this->assertSame($expectedPayload, $this->event->getPayload());
    }

    public function testGetVersion(): void
    {
        $userId = new UserId();
        $email = Email::fromString('test@example.com');
        $this->event = new UserLoggedInEvent($userId, $email, 'customer');
        $this->assertSame(1, $this->event->getVersion());
    }
}
