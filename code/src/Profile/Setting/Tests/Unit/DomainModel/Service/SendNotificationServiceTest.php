<?php

declare(strict_types=1);

namespace Profile\Setting\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Profile\Setting\DomainModel\Enum\VerificationType;
use Profile\Setting\DomainModel\Service\SendNotificationService;
use Profile\Setting\DomainModel\ValueObject\VerificationCode;
use Shared\DomainModel\Services\NotificationInterface;
use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[CoversClass(SendNotificationService::class)]
final class SendNotificationServiceTest extends TestCase
{
    private const int TTL = 300; // 5 minutes
    private MockObject&NotificationInterface $notification;
    private MockObject&Environment $twig;
    private MockObject&TranslatorInterface $translator;
    private SendNotificationService $service;

    protected function setUp(): void
    {
        $this->notification = $this->createMock(NotificationInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->service = new SendNotificationService(
            $this->notification,
            $this->twig,
            $this->translator,
            self::TTL
        );
    }

    public function testSendEmailVerificationCode(): void
    {
        $recipient = 'test@example.com';
        $code = VerificationCode::fromString('123456');
        $type = VerificationType::EMAIL;
        $htmlContent = '<html>Email template</html>';

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
                'code' => '123456',
                'title' => 'Verification Code',
                'message' => 'Your verification code',
                'expiry' => 'Code expires in 5 minutes',
                'ignore' => 'Ignore if not requested',
            ])
            ->willReturn($htmlContent);

        // Verify notification is sent
        $this->notification
            ->expects(self::once())
            ->method('send')
            ->with(
                self::callback(function (Notification $notification) use ($htmlContent) {
                    return $notification->content === $htmlContent
                        && 'Verification Code' === $notification->subject
                        && $notification->channels === ['custom-email'];
                }),
                self::callback(function (Recipient $recipient) {
                    return 'test@example.com' === $recipient->getEmail();
                })
            );

        $this->service->send($type, $recipient, $code);
    }

    public function testSendSmsVerificationCode(): void
    {
        $recipient = '+1234567890';
        $code = VerificationCode::fromString('123456');
        $type = VerificationType::PHONE;

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
                    return $notification->content === 'Your code: '.$code->toString()
                        && 'Verification Code' === $notification->subject
                        && $notification->channels === ['sms'];
                }),
                self::callback(function (Recipient $recipient) {
                    return '+1234567890' === $recipient->getPhone()
                        && 'null@domain.com' === $recipient->getEmail();
                })
            );

        $this->service->send($type, $recipient, $code);
    }
}
