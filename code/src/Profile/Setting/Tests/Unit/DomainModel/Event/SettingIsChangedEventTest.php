<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;
use Profile\Setting\DomainModel\Event\SettingIsChangedEvent;
use Profile\Setting\DomainModel\ValueObject\Property;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(SettingIsChangedEvent::class)]
final class SettingIsChangedEventTest extends TestCase
{
    private UserId $userId;
    private Property $property;

    protected function setUp(): void
    {
        $this->userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $this->property = new Property(
            PropertyCategory::from('GENERAL'),
            PropertyName::from('theme'),
            'public'
        );
    }

    public function testEventCreation(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertSame($this->userId, $event->id);
        self::assertSame($this->property, $event->property);
    }

    public function testGetAggregateId(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $event->getAggregateId());
    }

    public function testGetAggregateType(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertSame('Setting.settings.changed', $event->getAggregateType());
    }

    public function testGetPayload(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertSame([
            'category' => 'GENERAL',
            'name' => 'theme',
            'value' => 'public',
        ], $event->getPayload());
    }

    public function testGetVersion(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertSame(1, $event->getVersion());
    }

    public function testGetOccurredOn(): void
    {
        $event = new SettingIsChangedEvent($this->userId, $this->property);

        self::assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredOn());
    }
}
