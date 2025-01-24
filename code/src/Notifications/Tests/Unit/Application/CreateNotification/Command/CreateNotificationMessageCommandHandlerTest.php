<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\Application\CreateNotification\Command;

use Notifications\Application\CreateNotification\Command\CreateNotificationMessageCommand;
use Notifications\Application\CreateNotification\Command\CreateNotificationMessageCommandHandler;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\DomainModel\ValueObject\NotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(CreateNotificationMessageCommandHandler::class)]
final class CreateNotificationMessageCommandHandlerTest extends TestCase
{
    private NotificationServiceInterface&MockObject $notificationService;
    private CreateNotificationMessageCommandHandler $handler;

    protected function setUp(): void
    {
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);
        $this->handler = new CreateNotificationMessageCommandHandler($this->notificationService);
    }

    public function testInvokeCallsCreateNotificationWithCorrectParameters(): void
    {
        // Arrange
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userId = new UserId();
        $command = new CreateNotificationMessageCommand($notificationId, $userId);

        // Assert expectations
        $this->notificationService
            ->expects($this->once())
            ->method('createNotification')
            ->with(
                $this->identicalTo($notificationId),
                $this->identicalTo($userId)
            );

        // Act
        $this->handler->__invoke($command);
    }
}
