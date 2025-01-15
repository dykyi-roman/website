<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\Application\SettingsAccount\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\Application\SettingsAccount\Command\SendVerificationCodeCommand;
use Profile\Setting\Application\SettingsAccount\Command\SendVerificationCodeHandler;
use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Exception\VerificationException;
use Profile\Setting\DomainModel\Service\SendNotificationService;
use Profile\Setting\DomainModel\Service\VerificationService;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Profile\User\DomainModel\Enum\UserId;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\NotificationInterface;
use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[CoversClass(SendVerificationCodeHandler::class)]
final class SendVerificationCodeHandlerTest extends TestCase
{
    private MockObject&VerificationService $verificationService;
    private SendNotificationService $sendNotificationService;
    private MockObject&NotificationInterface $notification;
    private MockObject&Environment $twig;
    private MockObject&TranslatorInterface $translator;
    private MockObject&LoggerInterface $logger;
    private SendVerificationCodeHandler $handler;

    protected function setUp(): void
    {
        $this->verificationService = $this->createMock(VerificationService::class);
        $this->notification = $this->createMock(NotificationInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->sendNotificationService = new SendNotificationService(
            $this->notification,
            $this->twig,
            $this->translator,
            300 // 5 minutes TTL
        );

        $this->handler = new SendVerificationCodeHandler(
            $this->verificationService,
            $this->sendNotificationService,
            $this->logger
        );
    }

    public function testSuccessfulEmailCodeGeneration(): void
    {
        $userId = new UserId();
        $type = VerificationType::EMAIL->value;
        $recipient = 'test@example.com';
        $code = '123456';
        $verificationCode = VerificationCode::fromString($code);

        $command = new SendVerificationCodeCommand($userId, $type, $recipient);

        $this->verificationService
            ->expects(self::once())
            ->method('generateCode')
            ->with($userId->toRfc4122(), VerificationType::EMAIL)
            ->willReturn($verificationCode);

        // Setup translator expectations for email template
        $this->translator
            ->expects(self::exactly(5))
            ->method('trans')
            ->willReturnMap([
                ['settings.account.verification_code_title', [], null, null, 'Verification Code'],
                ['settings.account.verification_code_message', [], null, null, 'Your verification code'],
                ['settings.account.verification_code_expiry', ['%minutes%' => 5], null, null, 'Code expires in 5 minutes'],
                ['settings.account.verification_code_ignore', [], null, null, 'Ignore if not requested'],
            ]);

        // Setup twig template rendering
        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with('@Setting/email/verification-code.html.twig', [
                'code' => $code,
                'title' => 'Verification Code',
                'message' => 'Your verification code',
                'expiry' => 'Code expires in 5 minutes',
                'ignore' => 'Ignore if not requested',
            ])
            ->willReturn('<html>Email template</html>');

        // Verify notification is sent
        $this->notification
            ->expects(self::once())
            ->method('send')
            ->with(
                self::callback(function (Notification $notification) {
                    return '<html>Email template</html>' === $notification->content
                        && 'Verification Code' === $notification->subject
                        && $notification->channels === ['custom-email'];
                }),
                self::callback(function (Recipient $recipient) {
                    return 'test@example.com' === $recipient->getEmail();
                })
            );

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Verification code sent successfully',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                    'recipient' => $recipient,
                ]
            );

        $this->handler->__invoke($command);
    }

    public function testSuccessfulPhoneCodeGeneration(): void
    {
        $userId = new UserId();
        $type = VerificationType::PHONE->value;
        $recipient = '+1234567890';
        $code = '123456';
        $verificationCode = VerificationCode::fromString($code);

        $command = new SendVerificationCodeCommand($userId, $type, $recipient);

        $this->verificationService
            ->expects(self::once())
            ->method('generateCode')
            ->with($userId->toRfc4122(), VerificationType::PHONE)
            ->willReturn($verificationCode);

        // Setup translator expectations for SMS
        $this->translator
            ->expects(self::exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['settings.account.verification_code_title', [], null, null, 'Verification Code'],
                ['settings.account.your_verification_code', [], null, null, 'Your code'],
            ]);

        // Verify SMS notification is sent
        $this->notification
            ->expects(self::once())
            ->method('send')
            ->with(
                self::callback(function (Notification $notification) use ($code) {
                    return $notification->content === 'Your code: '.$code
                        && 'Verification Code' === $notification->subject
                        && $notification->channels === ['sms'];
                }),
                self::callback(function (Recipient $recipient) {
                    return '+1234567890' === $recipient->getPhone();
                })
            );

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'Verification code sent successfully',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                    'recipient' => $recipient,
                ]
            );

        $this->handler->__invoke($command);
    }

    public function testHandleVerificationException(): void
    {
        $userId = new UserId();
        $type = VerificationType::EMAIL->value;
        $recipient = 'test@example.com';
        $errorMessage = 'Verification failed';

        $command = new SendVerificationCodeCommand($userId, $type, $recipient);

        $this->verificationService
            ->expects(self::once())
            ->method('generateCode')
            ->willThrowException(new VerificationException($errorMessage));

        $this->notification
            ->expects(self::never())
            ->method('send');

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Failed to send verification code',
                [
                    'userId' => $userId->toRfc4122(),
                    'type' => $type,
                    'recipient' => $recipient,
                    'error' => $errorMessage,
                ]
            );

        $this->expectException(VerificationException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->handler->__invoke($command);
    }
}
