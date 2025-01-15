<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommand;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommandHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Model\User;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[CoversClass(DeleteUserAccountCommandHandler::class)]
final class DeleteUserAccountCommandHandlerTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&TokenStorageInterface $tokenStorage;
    private MockObject&LoggerInterface $logger;
    private DeleteUserAccountCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new DeleteUserAccountCommandHandler(
            $this->userRepository,
            $this->tokenStorage,
            $this->logger
        );
    }

    public function testSuccessfulUserDeletion(): void
    {
        $userId = new UserId();
        $command = new DeleteUserAccountCommand($userId);

        $user = $this->createMock(User::class);
        $user->expects(self::once())
            ->method('delete');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->tokenStorage
            ->expects(self::once())
            ->method('setToken')
            ->with(null);

        $this->logger
            ->expects(self::never())
            ->method('error');

        $this->handler->__invoke($command);
    }

    public function testHandleException(): void
    {
        $userId = new UserId();
        $command = new DeleteUserAccountCommand($userId);
        $errorMessage = 'User not found';

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->willThrowException(new \RuntimeException($errorMessage));

        $this->userRepository
            ->expects(self::never())
            ->method('save');

        $this->tokenStorage
            ->expects(self::never())
            ->method('setToken');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($errorMessage);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
