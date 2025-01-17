<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\ChangePassword\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\ChangePassword\Command\ChangePasswordCommand;
use Profile\Setting\Application\ChangePassword\Command\ChangePasswordCommandHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(ChangePasswordCommandHandler::class)]
final class ChangePasswordCommandHandlerTest extends TestCase
{
    private ChangePasswordCommandHandler $handler;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserRepositoryInterface&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private User&MockObject $user;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->user = $this->createMock(User::class);

        $this->handler = new ChangePasswordCommandHandler(
            $this->passwordHasher,
            $this->userRepository,
            $this->logger
        );
    }

    public function testSuccessfulPasswordChange(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new ChangePasswordCommand(
            $userId,
            'currentPassword',
            'newPassword'
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, 'currentPassword')
            ->willReturn(true);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->user, 'newPassword')
            ->willReturn('hashedPassword');

        $this->user->expects($this->once())
            ->method('updatePassword')
            ->with('hashedPassword');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->user);

        $this->handler->__invoke($command);
    }

    public function testInvalidCurrentPassword(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new ChangePasswordCommand(
            $userId,
            'wrongPassword',
            'newPassword'
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, 'wrongPassword')
            ->willReturn(false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->handler->__invoke($command);
    }

    public function testPasswordChangeFailure(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new ChangePasswordCommand(
            $userId,
            'currentPassword',
            'newPassword'
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($this->user, 'currentPassword')
            ->willReturn(true);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->user, 'newPassword')
            ->willReturn('hashedPassword');

        $this->user->expects($this->once())
            ->method('updatePassword')
            ->with('hashedPassword')
            ->willThrowException(new \RuntimeException('Failed to update password'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Password change failed',
                $this->callback(function (array $context) use ($userId) {
                    return $context['userId'] === $userId->toRfc4122()
                        && 'newPassword' === $context['newPassword']
                        && 'Failed to update password' === $context['error'];
                })
            );

        $this->handler->__invoke($command);
    }
}
