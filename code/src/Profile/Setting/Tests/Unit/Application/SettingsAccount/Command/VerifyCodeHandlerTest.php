<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsAccount\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsAccount\Command\VerifyCodeCommand;
use Profile\Setting\Application\SettingsAccount\Command\VerifyCodeHandler;
use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Exception\VerificationException;
use Profile\Setting\DomainModel\Service\VerificationService;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Profile\User\Application\UserVerification\Command\VerifyUserEmailCommand;
use Profile\User\Application\UserVerification\Command\VerifyUserPhoneCommand;
use Profile\User\DomainModel\Enum\UserId;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;

#[CoversClass(VerifyCodeHandler::class)]
final class VerifyCodeHandlerTest extends TestCase
{
    private MockObject&VerificationService $verificationService;
    private MockObject&MessageBusInterface $messageBus;
    private MockObject&LoggerInterface $logger;
    private VerifyCodeHandler $handler;

    protected function setUp(): void
    {
        $this->verificationService = $this->createMock(VerificationService::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->handler = new VerifyCodeHandler(
            $this->verificationService,
            $this->messageBus,
            $this->logger
        );
    }

    public function testSuccessfulEmailVerification(): void
    {
        $userId = new UserId();
        $type = VerificationType::EMAIL->value;
        $code = '123456';

        $command = new VerifyCodeCommand($userId, $type, $code);
        $verificationCode = VerificationCode::fromString($code);

        $this->verificationService
            ->expects(self::once())
            ->method('verifyCode')
            ->with($userId->toRfc4122(), VerificationType::EMAIL, $verificationCode)
            ->willReturn(true);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(VerifyUserEmailCommand::class));

        $this->verificationService
            ->expects(self::once())
            ->method('invalidateCode')
            ->with($userId->toRfc4122(), VerificationType::EMAIL);

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Verification completed successfully',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                ]
            );

        $this->handler->__invoke($command);
    }

    public function testSuccessfulPhoneVerification(): void
    {
        $userId = new UserId();
        $type = VerificationType::PHONE->value;
        $code = '123456';

        $command = new VerifyCodeCommand($userId, $type, $code);
        $verificationCode = VerificationCode::fromString($code);

        $this->verificationService
            ->expects(self::once())
            ->method('verifyCode')
            ->with($userId->toRfc4122(), VerificationType::PHONE, $verificationCode)
            ->willReturn(true);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(VerifyUserPhoneCommand::class));

        $this->verificationService
            ->expects(self::once())
            ->method('invalidateCode')
            ->with($userId->toRfc4122(), VerificationType::PHONE);

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Verification completed successfully',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                ]
            );

        $this->handler->__invoke($command);
    }

    public function testInvalidCodeVerification(): void
    {
        $userId = new UserId();
        $type = VerificationType::EMAIL->value;
        $code = '123456';

        $command = new VerifyCodeCommand($userId, $type, $code);
        $verificationCode = VerificationCode::fromString($code);

        $this->verificationService
            ->expects(self::once())
            ->method('verifyCode')
            ->with($userId->toRfc4122(), VerificationType::EMAIL, $verificationCode)
            ->willReturn(false);

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->verificationService
            ->expects(self::never())
            ->method('invalidateCode');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Verification failed',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                    'error' => 'Invalid verification code',
                ]
            );

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage('Invalid verification code');

        $this->handler->__invoke($command);
    }

    public function testVerificationServiceException(): void
    {
        $userId = new UserId();
        $type = VerificationType::EMAIL->value;
        $code = '123456';
        $errorMessage = 'Verification service error';

        $command = new VerifyCodeCommand($userId, $type, $code);
        $verificationCode = VerificationCode::fromString($code);

        $this->verificationService
            ->expects(self::once())
            ->method('verifyCode')
            ->with($userId->toRfc4122(), VerificationType::EMAIL, $verificationCode)
            ->willThrowException(new VerificationException($errorMessage));

        $this->messageBus
            ->expects(self::never())
            ->method('dispatch');

        $this->verificationService
            ->expects(self::never())
            ->method('invalidateCode');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Verification failed',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                    'error' => $errorMessage,
                ]
            );

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
