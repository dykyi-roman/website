<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserAuthentication\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserAuthentication\Command\CreateUserPasswordCommand;
use Profile\User\Application\UserAuthentication\Command\CreateUserPasswordCommandHandler;
use Profile\User\Application\UserManagement\Service\PasswordChangeServiceInterface;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(CreateUserPasswordCommandHandler::class)]
final class CreateUserPasswordHandlerTest extends TestCase
{
    /** @var UserRepositoryInterface&MockObject */
    private UserRepositoryInterface $userRepository;

    /** @var PasswordChangeServiceInterface&MockObject */
    private PasswordChangeServiceInterface $passwordHasher;

    private CreateUserPasswordCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordChangeServiceInterface::class);
        $this->handler = new CreateUserPasswordCommandHandler(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    public function testThrowExceptionWhenPasswordsAreNotEqual(): void
    {
        $command = new CreateUserPasswordCommand(
            new UserId(),
            'password13',
            'password123'
        );

        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)($command);
    }

    public function testSuccessfulPasswordChange(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);
        $command = new CreateUserPasswordCommand(
            $userId,
            'newPassword123',
            'newPassword123'
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('change')
            ->with($user, 'newPassword123');

        ($this->handler)($command);
    }
}
