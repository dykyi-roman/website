<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserAuthentication\Command;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserAuthentication\Command\CreateUserPassword;
use Profile\User\Application\UserAuthentication\Command\CreateUserPasswordHandler;
use Profile\User\Application\UserManagement\Service\PasswordChangeServiceInterface;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;

#[CoversClass(CreateUserPasswordHandler::class)]
final class CreateUserPasswordHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private PasswordChangeServiceInterface|MockObject $passwordHasher;
    private CreateUserPasswordHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordChangeServiceInterface::class);
        $this->handler = new CreateUserPasswordHandler(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    public function testThrowExceptionWhenPasswordsAreEqual(): void
    {
        $command = new CreateUserPassword(
            new UserId(),
            'password123',
            'password123'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Current password is incorrect');

        ($this->handler)($command);
    }

    public function testSuccessfulPasswordChange(): void
    {
        $userId =  new UserId();
        $user = $this->createMock(UserInterface::class);
        $command = new CreateUserPassword(
            $userId,
            'newPassword123',
            'differentPassword123'
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