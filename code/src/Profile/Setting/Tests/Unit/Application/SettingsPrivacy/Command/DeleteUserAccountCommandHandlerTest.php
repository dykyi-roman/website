<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommand;
use Profile\Setting\Application\SettingsPrivacy\Command\DeleteUserAccountCommandHandler;
use Profile\User\Application\UserPrivacyOperation\Service\UserPrivacyServiceInterface;
use Profile\User\DomainModel\Enum\UserId;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[CoversClass(DeleteUserAccountCommandHandler::class)]
final class DeleteUserAccountCommandHandlerTest extends TestCase
{
    private MockObject&UserPrivacyServiceInterface $userPrivacyService;
    private MockObject&TokenStorageInterface $tokenStorage;
    private DeleteUserAccountCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userPrivacyService = $this->createMock(UserPrivacyServiceInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->handler = new DeleteUserAccountCommandHandler(
            $this->userPrivacyService,
            $this->tokenStorage,
        );
    }

    public function testSuccessfulUserDeletion(): void
    {
        $userId = new UserId();
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

    public function testHandleException(): void
    {
        $userId = new UserId();
        $command = new DeleteUserAccountCommand($userId);
        $errorMessage = 'User not found';
        $exception = new \RuntimeException($errorMessage);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('delete')
            ->with($userId)
            ->willThrowException($exception);

        $this->tokenStorage
            ->expects(self::never())
            ->method('setToken');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
