<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserManagement\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserManagement\Exception\UserChangeDataException;
use Profile\User\Application\UserManagement\Exception\UserExistException;
use Profile\User\Application\UserManagement\Service\UpdateUserService;
use Profile\User\DomainModel\Exception\UserNotFoundException;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\UserId;

#[CoversClass(UpdateUserService::class)]
final class UpdateUserServiceTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&LoggerInterface $logger;
    private UpdateUserService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new UpdateUserService($this->userRepository, $this->logger);
    }

    public function testSuccessfulUpdateWithAllFields(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = 'john@example.com';
        $phone = '+1234567890';
        $avatar = 'avatar.jpg';

        $user = $this->createMock(UserInterface::class);
        $newEmail = Email::fromString($email);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn(null);

        $user->expects(self::once())
            ->method('changeName')
            ->with($name);

        $user->expects(self::once())
            ->method('changeEmail')
            ->with($newEmail);

        $user->expects(self::once())
            ->method('changePhone')
            ->with($phone);

        $user->expects(self::once())
            ->method('changeAvatar')
            ->with($avatar);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->service->update($userId, $name, $email, $phone, $avatar);
    }

    public function testSuccessfulUpdateWithoutAvatar(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = 'john@example.com';
        $phone = '+1234567890';

        $user = $this->createMock(UserInterface::class);
        $newEmail = Email::fromString($email);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn(null);

        $user->expects(self::once())
            ->method('changeName')
            ->with($name);

        $user->expects(self::once())
            ->method('changeEmail')
            ->with($newEmail);

        $user->expects(self::once())
            ->method('changePhone')
            ->with($phone);

        $user->expects(self::never())
            ->method('changeAvatar');

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->service->update($userId, $name, $email, $phone);
    }

    public function testUpdateFailsWhenEmailAlreadyExists(): void
    {
        // Arrange
        $userId = new UserId();
        $email = 'john@example.com';
        $oldEmail = Email::fromString('old@example.com');
        $newEmail = Email::fromString($email);

        $user = $this->createMock(UserInterface::class);
        $user->method('email')
            ->willReturn($oldEmail);
        $user->method('id')
            ->willReturn($userId);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn($user);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('Attempted to update user with existing email');

        // Act & Assert
        $this->expectException(UserExistException::class);
        $this->service->update($userId, 'John Doe', $email, '+1234567890');
    }

    public function testUpdateFailsWhenUserNotFound(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = 'john@example.com';
        $phone = '+1234567890';

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willThrowException(new UserNotFoundException($userId));

        $this->expectException(UserNotFoundException::class);
        $this->service->update($userId, $name, $email, $phone);
    }

    public function testUpdateFailsOnError(): void
    {
        $userId = new UserId();
        $name = 'John Doe';
        $email = 'john@example.com';
        $phone = '+1234567890';

        $user = $this->createMock(UserInterface::class);
        $newEmail = Email::fromString($email);
        $error = new \RuntimeException('Update failed');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn(null);

        $user->expects(self::once())
            ->method('changeName')
            ->with($name)
            ->willThrowException($error);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($error->getMessage());

        $this->expectException(UserChangeDataException::class);
        $this->service->update($userId, $name, $email, $phone);
    }
}
