<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\UserPrivacyOperation\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\UserPrivacyOperation\Service\UserPrivacyService;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\ActivateUserException;
use Profile\User\DomainModel\Exception\DeactivateUserException;
use Profile\User\DomainModel\Exception\DeleteUserException;
use Profile\User\DomainModel\Exception\UserNotFoundException;
use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

#[CoversClass(UserPrivacyService::class)]
final class UserPrivacyServiceTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&LoggerInterface $logger;
    private UserPrivacyService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new UserPrivacyService($this->userRepository, $this->logger);
    }

    public function testSuccessfulDelete(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('delete');

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->service->delete($userId);
    }

    public function testDeleteFailsWhenUserNotFound(): void
    {
        $userId = new UserId();

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willThrowException(new UserNotFoundException($userId));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isType('string'));

        $this->expectException(DeleteUserException::class);
        $this->service->delete($userId);
    }

    public function testDeleteFailsOnError(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);
        $error = new \RuntimeException('Delete failed');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('delete')
            ->willThrowException($error);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($error->getMessage());

        $this->expectException(DeleteUserException::class);
        $this->service->delete($userId);
    }

    public function testSuccessfulActivate(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('activate');

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->service->activate($userId);
    }

    public function testActivateFailsWhenUserNotFound(): void
    {
        $userId = new UserId();

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willThrowException(new UserNotFoundException($userId));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isType('string'));

        $this->expectException(ActivateUserException::class);
        $this->service->activate($userId);
    }

    public function testActivateFailsOnError(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);
        $error = new \RuntimeException('Activation failed');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('activate')
            ->willThrowException($error);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($error->getMessage());

        $this->expectException(ActivateUserException::class);
        $this->service->activate($userId);
    }

    public function testSuccessfulDeactivate(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('deactivate');

        $this->userRepository
            ->expects(self::once())
            ->method('save')
            ->with($user);

        $this->service->deactivate($userId);
    }

    public function testDeactivateFailsWhenUserNotFound(): void
    {
        $userId = new UserId();

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willThrowException(new UserNotFoundException($userId));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(self::isType('string'));

        $this->expectException(DeactivateUserException::class);
        $this->service->deactivate($userId);
    }

    public function testDeactivateFailsOnError(): void
    {
        $userId = new UserId();
        $user = $this->createMock(UserInterface::class);
        $error = new \RuntimeException('Deactivation failed');

        $this->userRepository
            ->expects(self::once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $user->expects(self::once())
            ->method('deactivate')
            ->willThrowException($error);

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with($error->getMessage());

        $this->expectException(DeactivateUserException::class);
        $this->service->deactivate($userId);
    }
}
