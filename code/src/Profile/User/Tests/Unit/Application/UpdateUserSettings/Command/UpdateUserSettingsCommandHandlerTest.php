<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UpdateUserSettings\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UpdateUserSettings\Command\UpdateUserSettingsCommand;
use Profile\User\Application\UpdateUserSettings\Command\UpdateUserSettingsCommandHandler;
use Profile\User\Application\UpdateUserSettings\Exception\UserExistException;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\Email;

#[CoversClass(UpdateUserSettingsCommandHandler::class)]
final class UpdateUserSettingsCommandHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private UpdateUserSettingsCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->handler = new UpdateUserSettingsCommandHandler($this->userRepository);
    }

    public function testSuccessfulUpdate(): void
    {
        $userId = new UserId();
        $currentEmail = Email::fromString('same@example.com');
        $newEmail = Email::fromString('same@example.com');

        $user = $this->createMock(User::class);
        $user->expects(self::once())->method('email')->willReturn($currentEmail);
        $user->expects(self::once())->method('changeName')->with('John Doe');
        $user->expects(self::once())->method('changeEmail')->with($newEmail);
        $user->expects(self::once())->method('changePhone')->with('+1234567890');
        $user->expects(self::once())->method('changeAvatar')->with('avatar.jpg');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn($this->createMock(UserInterface::class));

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'same@example.com',
            phone: '+1234567890',
            avatar: 'avatar.jpg'
        );

        $this->handler->__invoke($command);
    }

    public function testUpdateWithoutAvatar(): void
    {
        $userId = new UserId();
        $email = Email::fromString('test@example.com');

        $user = $this->createMock(User::class);
        $user->expects(self::once())->method('email')->willReturn($email);
        $user->expects(self::once())->method('changeName')->with('John Doe');
        $user->expects(self::once())->method('changeEmail')->with($email);
        $user->expects(self::once())->method('changePhone')->with('+1234567890');
        $user->expects(self::never())->method('changeAvatar');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($this->createMock(UserInterface::class));

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'test@example.com',
            phone: '+1234567890'
        );

        $this->handler->__invoke($command);
    }

    public function testThrowsExceptionWhenEmailAlreadyExists(): void
    {
        $userId = new UserId();
        $currentEmail = Email::fromString('old@example.com');
        $newEmail = Email::fromString('new@example.com');

        $user = $this->createMock(User::class);
        $user->expects(self::once())->method('email')->willReturn($currentEmail);
        $user->expects(self::once())->method('id')->willReturn($userId);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn($this->createMock(UserInterface::class));

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'new@example.com',
            phone: '+1234567890'
        );

        $this->expectException(UserExistException::class);
        $this->handler->__invoke($command);
    }

    public function testAllowsSameEmailUpdate(): void
    {
        $userId = new UserId();
        $currentEmail = Email::fromString('same@example.com');
        $newEmail = Email::fromString('same@example.com');

        $user = $this->createMock(User::class);
        $user->expects(self::once())->method('email')->willReturn($currentEmail);
        $user->expects(self::once())->method('changeName')->with('John Doe');
        $user->expects(self::once())->method('changeEmail')->with($newEmail);
        $user->expects(self::once())->method('changePhone')->with('+1234567890');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('findByEmail')
            ->with($newEmail)
            ->willReturn($this->createMock(UserInterface::class));

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $command = new UpdateUserSettingsCommand(
            userId: $userId,
            name: 'John Doe',
            email: 'same@example.com',
            phone: '+1234567890'
        );

        $this->handler->__invoke($command);
    }
}
