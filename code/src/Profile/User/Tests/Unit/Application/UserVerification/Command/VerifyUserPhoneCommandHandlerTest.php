<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\VerifyUserProfile\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserVerification\Command\VerifyUserPhoneCommand;
use Profile\User\Application\UserVerification\Command\VerifyUserPhoneCommandHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

final class VerifyUserPhoneCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private VerifyUserPhoneCommandHandler $handler;
    private UserInterface&MockObject $user;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->handler = new VerifyUserPhoneCommandHandler(
            $this->userRepository,
            $this->logger
        );
    }

    public function testSuccessfulPhoneVerification(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new VerifyUserPhoneCommand($userId);

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('verifyPhone');

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
        $command = new VerifyUserPhoneCommand($userId);
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
        $command = new VerifyUserPhoneCommand($userId);
        $exception = new \RuntimeException('Verification failed');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->user
            ->expects($this->once())
            ->method('verifyPhone')
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
