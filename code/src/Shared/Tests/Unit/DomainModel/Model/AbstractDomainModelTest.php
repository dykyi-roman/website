<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\DomainModel\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\DomainModel\Model\AbstractDomainModel;

#[CoversClass(AbstractDomainModel::class)]
final class AbstractDomainModelTest extends TestCase
{
    private AbstractDomainModel $model;

    protected function setUp(): void
    {
        // Create concrete implementation of abstract class for testing
        $this->model = new class extends AbstractDomainModel {
        };
    }

    public function testRaiseShouldAddEventToCollection(): void
    {
        // Arrange
        /** @var DomainEventInterface&\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(DomainEventInterface::class);

        // Act
        $this->model->raise($event);

        // Assert
        $events = $this->model->releaseEvents();
        self::assertCount(1, $events);
        self::assertSame($event, $events[0]);
    }

    public function testReleaseEventsShouldReturnAndClearEvents(): void
    {
        // Arrange
        /** @var DomainEventInterface&\PHPUnit\Framework\MockObject\MockObject $event1 */
        $event1 = $this->createMock(DomainEventInterface::class);
        /** @var DomainEventInterface&\PHPUnit\Framework\MockObject\MockObject $event2 */
        $event2 = $this->createMock(DomainEventInterface::class);

        $this->model->raise($event1);
        $this->model->raise($event2);

        // Act
        $firstRelease = $this->model->releaseEvents();
        $secondRelease = $this->model->releaseEvents();

        // Assert
        self::assertCount(2, $firstRelease);
        self::assertSame($event1, $firstRelease[0]);
        self::assertSame($event2, $firstRelease[1]);
        self::assertEmpty($secondRelease, 'Second release should return empty array as events were cleared');
    }
}
