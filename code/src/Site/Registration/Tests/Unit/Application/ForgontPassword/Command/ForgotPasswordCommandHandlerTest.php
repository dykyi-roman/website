<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\Application\ForgontPassword\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\UserId;
use Site\Registration\Application\ForgontPassword\Command\ForgotPasswordCommand;
use Site\Registration\Application\ForgontPassword\Command\ForgotPasswordCommandHandler;
use Site\Registration\DomainModel\Service\PasswordResetNotificationInterface;
use Site\Registration\DomainModel\Service\TokenGeneratorInterface;

#[CoversClass(ForgotPasswordCommandHandler::class)]
final class ForgotPasswordCommandHandlerTest extends TestCase
{
    private ForgotPasswordCommandHandler $handler;
    private UserRepositoryInterface&MockObject $userRepository;
    private TokenGeneratorInterface&MockObject $tokenGenerator;
    private PasswordResetNotificationInterface&MockObject $passwordResetNotification;
    private LoggerInterface&MockObject $logger;
    private User&MockObject $user;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->passwordResetNotification = $this->createMock(PasswordResetNotificationInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(User::class);

        $this->handler = new ForgotPasswordCommandHandler(
            $this->userRepository,
            $this->tokenGenerator,
            $this->passwordResetNotification,
            $this->logger
        );
    }

    public function testSuccessfulPasswordReset(): void
    {
        $email = 'test@example.com';
        $command = new ForgotPasswordCommand($email);
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $token = 'generated_token';
        $userName = 'John Doe';

        $this->user->method('isDeleted')->willReturn(false);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('id')->willReturn($userId);
        $this->user->method('name')->willReturn($userName);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => $e->__toString() === $email))
            ->willReturn($this->user);

        $this->tokenGenerator->expects($this->once())
            ->method('generate')
            ->with($userId->toBase32())
            ->willReturn($token);

        $this->user->expects($this->once())
            ->method('setPasswordToken')
            ->with($token);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->passwordResetNotification->expects($this->once())
            ->method('send')
            ->with($email, $userName, $token);

        $this->handler->__invoke($command);
    }

    public function testUserNotFound(): void
    {
        $email = 'nonexistent@example.com';
        $command = new ForgotPasswordCommand($email);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => $e->__toString() === $email))
            ->willReturn(null);

        $this->tokenGenerator->expects($this->never())->method('generate');
        $this->passwordResetNotification->expects($this->never())->method('send');

        $this->handler->__invoke($command);
    }

    public function testDeletedUser(): void
    {
        $email = 'deleted@example.com';
        $command = new ForgotPasswordCommand($email);

        $this->user->method('isDeleted')->willReturn(true);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => $e->__toString() === $email))
            ->willReturn($this->user);

        $this->tokenGenerator->expects($this->never())->method('generate');
        $this->passwordResetNotification->expects($this->never())->method('send');

        $this->handler->__invoke($command);
    }

    public function testInactiveUser(): void
    {
        $email = 'inactive@example.com';
        $command = new ForgotPasswordCommand($email);

        $this->user->method('isDeleted')->willReturn(false);
        $this->user->method('isActive')->willReturn(false);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => $e->__toString() === $email))
            ->willReturn($this->user);

        $this->tokenGenerator->expects($this->never())->method('generate');
        $this->passwordResetNotification->expects($this->never())->method('send');

        $this->handler->__invoke($command);
    }

    public function testExceptionHandling(): void
    {
        $email = 'test@example.com';
        $command = new ForgotPasswordCommand($email);
        $userId = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $exception = new \RuntimeException('Token generation failed');

        $this->user->method('isDeleted')->willReturn(false);
        $this->user->method('isActive')->willReturn(true);
        $this->user->method('id')->willReturn($userId);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn (Email $e) => $e->__toString() === $email))
            ->willReturn($this->user);

        $this->tokenGenerator->expects($this->once())
            ->method('generate')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Password reset failed',
                [
                    'email' => $email,
                    'error' => 'Token generation failed',
                ]
            );

        $this->handler->__invoke($command);
    }
}
