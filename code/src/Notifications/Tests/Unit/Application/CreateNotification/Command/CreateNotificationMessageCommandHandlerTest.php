<?php

declare(strict_types=1);

namespace Notifications\Tests\Unit\Application\CreateNotification\Command;

use Notifications\Application\CreateNotification\Command\CreateUserNotificationCommand;
use Notifications\Application\CreateNotification\Command\CreateUserNotificationCommandHandler;
use Notifications\DomainModel\Service\NotificationServiceInterface;
use Notifications\DomainModel\ValueObject\NotificationId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(CreateUserNotificationCommandHandler::class)]
final class CreateNotificationMessageCommandHandlerTest extends TestCase
{
    private NotificationServiceInterface&MockObject $notificationService;
    private CreateUserNotificationCommandHandler $handler;

    protected function setUp(): void
    {
        $this->notificationService = $this->createMock(NotificationServiceInterface::class);
        $this->handler = new CreateUserNotificationCommandHandler($this->notificationService);
    }

    public function testInvokeCallsCreateNotificationWithCorrectParameters(): void
    {
        // Arrange
        $notificationId = NotificationId::HAPPY_NEW_YEAR;
        $userId = new UserId();
        $command = new CreateUserNotificationCommand($notificationId, $userId);

        // Assert expectations
        $this->notificationService
            ->expects($this->once())
            ->method('createUserNotification')
            ->with(
                $this->identicalTo($notificationId),
                $this->identicalTo($userId)
            );

        // Act
        $this->handler->__invoke($command);
    }
}
