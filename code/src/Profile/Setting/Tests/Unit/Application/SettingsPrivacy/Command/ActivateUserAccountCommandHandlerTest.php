<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsPrivacy\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommand;
use Profile\Setting\Application\SettingsPrivacy\Command\ActivateUserAccountCommandHandler;
use Profile\User\Application\UserPrivacyOperation\Service\UserPrivacyServiceInterface;
use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Enum\UserStatus;

#[CoversClass(ActivateUserAccountCommandHandler::class)]
final class ActivateUserAccountCommandHandlerTest extends TestCase
{
    private MockObject&UserPrivacyServiceInterface $userPrivacyService;
    private ActivateUserAccountCommandHandler $handler;

    protected function setUp(): void
    {
        $this->userPrivacyService = $this->createMock(UserPrivacyServiceInterface::class);
        $this->handler = new ActivateUserAccountCommandHandler(
            $this->userPrivacyService,
        );
    }

    public function testSuccessfulUserActivation(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::ACTIVATED);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('activate')
            ->with($userId);

        $this->handler->__invoke($command);
    }

    public function testSuccessfulUserDeactivation(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::DEACTIVATED);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('deactivate')
            ->with($userId);

        $this->handler->__invoke($command);
    }

    public function testHandleException(): void
    {
        $userId = new UserId();
        $command = new ActivateUserAccountCommand($userId, UserStatus::ACTIVATED);
        $errorMessage = 'User not found';
        $exception = new \RuntimeException($errorMessage);

        $this->userPrivacyService
            ->expects(self::once())
            ->method('activate')
            ->with($userId)
            ->willThrowException($exception);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
