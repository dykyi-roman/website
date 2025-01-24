<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserVerification\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserVerification\Command\VerifyUserEmailCommand;
use Profile\User\Application\UserVerification\Command\VerifyUserEmailCommandHandler;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\UserId;

final class VerifyUserEmailCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private VerifyUserEmailCommandHandler $handler;
    private UserInterface&MockObject $user;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->handler = new VerifyUserEmailCommandHandler(
            $this->userRepository,
            $this->logger
        );
    }

    public function testSuccessfulEmailVerification(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new VerifyUserEmailCommand($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('verifyEmail');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->logger
            ->expects($this->never())
            ->method('error');

        $this->handler->__invoke($command);
    }

    public function testLogsAndRethrowsExceptionWhenUserNotFound(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new VerifyUserEmailCommand($userId);
        $exception = new \RuntimeException('User not found');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('User not found');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User not found');

        $this->handler->__invoke($command);
    }

    public function testLogsAndRethrowsExceptionWhenVerificationFails(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new VerifyUserEmailCommand($userId);
        $exception = new \RuntimeException('Verification failed');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('verifyEmail')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Verification failed');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Verification failed');

        $this->handler->__invoke($command);
    }
}
