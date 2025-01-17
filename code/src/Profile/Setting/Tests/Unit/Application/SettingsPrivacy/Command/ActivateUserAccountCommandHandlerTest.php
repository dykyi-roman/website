<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommand;
use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommandHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Enum\UserStatus;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(ActivateUserAccountCommandHandler::class)]
final class ActivateUserAccountCommandHandlerTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&LoggerInterface $logger;
    private ActivateUserAccountCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new ActivateUserAccountCommandHandler(
            $this->userRepository,
        );
    }

    public function testSuccessfulUserActivation(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::ACTIVATED);

        $user = $this->createMock(User::class);
        $user->expects(self::once())
            ->method('activate');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->logger
            ->expects(self::never())
            ->method('error');

        $this->handler->__invoke($command);
    }

    public function testSuccessfulUserDeactivation(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::DEACTIVATED);

        $user = $this->createMock(User::class);
        $user->expects(self::once())
            ->method('deactivate');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->logger
            ->expects(self::never())
            ->method('error');

        $this->handler->__invoke($command);
    }

    public function testHandleException(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::ACTIVATED);
        $errorMessage = 'User not found';

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->willThrowException(new \RuntimeException($errorMessage));

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($errorMessage);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
