<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\ChangePassword\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\ChangePassword\Command\ChangePasswordCommand;
use Profile\User\Application\ChangePassword\Command\ChangePasswordCommandHandler;
use Profile\User\Application\ChangePassword\Service\PasswordChangeServiceInterface;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;

final class ChangePasswordCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private PasswordChangeServiceInterface&MockObject $passwordHasher;
    private ChangePasswordCommandHandler $handler;
    private UserInterface&MockObject $user;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordChangeServiceInterface::class);
        $this->user = $this->createMock(UserInterface::class);
        $this->handler = new ChangePasswordCommandHandler(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    public function testSuccessfulPasswordChange(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new ChangePasswordCommand($userId, 'current-password', 'new-password');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isValid')
            ->with($this->user, 'current-password')
            ->willReturn(true);

        $this->passwordHasher
            ->expects($this->once())
            ->method('change')
            ->with($this->user, 'new-password');

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionWhenCurrentPasswordIsIncorrect(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $command = new ChangePasswordCommand($userId, 'wrong-password', 'new-password');

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($this->user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isValid')
            ->with($this->user, 'wrong-password')
            ->willReturn(false);

        $this->passwordHasher
            ->expects($this->never())
            ->method('change');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Current password is incorrect');

        $this->handler->__invoke($command);
    }
}
