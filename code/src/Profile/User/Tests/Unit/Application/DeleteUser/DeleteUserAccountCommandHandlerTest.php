<?php

declare(strict_types=1);

namespace Profile\User\Tests\Unit\Application\DeleteUser;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\User\Application\DeleteUser\Command\DeleteUserAccountCommand;
use Profile\User\Application\DeleteUser\Command\DeleteUserAccountCommandHandler;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\DeleteUserException;
use Profile\User\DomainModel\Service\UserPrivacyServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class DeleteUserAccountCommandHandlerTest extends TestCase
{
    /** @var UserPrivacyServiceInterface&MockObject */
    private MockObject $userPrivacyService;
    /** @var TokenStorageInterface&MockObject */
    private MockObject $tokenStorage;
    private DeleteUserAccountCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userPrivacyService = $this->createMock(UserPrivacyServiceInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->handler = new DeleteUserAccountCommandHandler(
            $this->userPrivacyService,
            $this->tokenStorage
        );
    }

    public function testSuccessfulUserDeletion(): void
    {
        $userId = UserId::fromString('user-123');
        $command = new DeleteUserAccountCommand($userId);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('delete')
            ->with($userId);

        $this->tokenStorage
            ->expects(self::once())
            ->method('setToken')
            ->with(null);

        $this->handler->__invoke($command);
    }

    public function testFailedUserDeletion(): void
    {
        $userId = UserId::fromString('user-123');
        $command = new DeleteUserAccountCommand($userId);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('delete')
            ->with($userId)
            ->willThrowException(new DeleteUserException($userId));

        $this->expectException(DeleteUserException::class);
        $this->expectExceptionMessage(sprintf('User not deleted by id: %s', $userId->toRfc4122()));

        $this->handler->__invoke($command);
    }
}
