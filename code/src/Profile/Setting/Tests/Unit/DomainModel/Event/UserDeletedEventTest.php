<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Event\UserDeletedEvent;
use Profile\User\DomainModel\Enum\UserId;
use Shared\DomainModel\ValueObject\Email;

#[CoversClass(UserDeletedEvent::class)]
final class UserDeletedEventTest extends TestCase
{
    private UserId $userId;
    private Email $email;
    private string $name;

    protected function setUp(): void
    {
        $this->userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $this->email = Email::fromString('john.doe@example.com');
        $this->name = 'John Doe';
    }

    public function testEventCreation(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertSame($this->userId, $event->id);
        self::assertSame($this->email, $event->email);
        self::assertSame($this->name, $event->name);
    }

    public function testGetAggregateId(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertSame('user.deleted', $event->getAggregateType());
    }

    public function testGetPayload(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertSame([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ], $event->getPayload());
    }

    public function testGetVersion(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertSame(1, $event->getVersion());
    }

    public function testGetOccurredOn(): void
    {
        $event = new UserDeletedEvent($this->userId, $this->email, $this->name);

        self::assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }
}
