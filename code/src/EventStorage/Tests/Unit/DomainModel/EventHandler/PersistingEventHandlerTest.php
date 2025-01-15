<?php

declare(strict_types=1);

namespace EventStorage\Tests\Unit\DomainModel\EventHandler;

use EventStorage\DomainModel\Event\PersistingEventInterface;
use EventStorage\DomainModel\EventHandler\PersistingEventHandler;
use EventStorage\DomainModel\Model\Event;
use EventStorage\DomainModel\Repository\EventRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(PersistingEventHandler::class)]
final class PersistingEventHandlerTest extends TestCase
{
    /** @var MockObject&EventRepositoryInterface */
    private MockObject $eventRepository;

    /** @var MockObject&PersistingEventInterface */
    private MockObject $persistingEvent;
    private PersistingEventHandler $handler;

    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepositoryInterface::class);
        $this->persistingEvent = $this->createMock(PersistingEventInterface::class);
        $this->handler = new PersistingEventHandler($this->eventRepository);
    }

    public function testInvokeShouldPersistEventSuccessfully(): void
    {
        // Arrange
        $aggregateId = 'test-aggregate-id';
        $aggregateType = 'test-aggregate-type';
        $payload = ['key' => 'value'];
        $occurredOn = new \DateTimeImmutable();
        $version = 1;

        $this->persistingEvent
            ->expects($this->once())
            ->method('getAggregateId')
            ->willReturn($aggregateId);

        $this->persistingEvent
            ->expects($this->once())
            ->method('getAggregateType')
            ->willReturn($aggregateType);

        $this->persistingEvent
            ->expects($this->once())
            ->method('getPayload')
            ->willReturn($payload);

        $this->persistingEvent
            ->expects($this->once())
            ->method('getOccurredOn')
            ->willReturn($occurredOn);

        $this->persistingEvent
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Event $event) use ($aggregateId, $aggregateType, $payload, $occurredOn, $version) {
                return $event->getModelId() === $aggregateId
                    && $event->getType() === $aggregateType
                    && $event->getPayload() === $payload
                    && $event->getOccurredOn() === $occurredOn
                    && $event->getVersion() === $version;
            }));

        // Act
        $this->handler->__invoke($this->persistingEvent);
    }
}
