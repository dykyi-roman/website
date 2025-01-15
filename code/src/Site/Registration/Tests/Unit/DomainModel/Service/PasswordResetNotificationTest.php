<?php

declare(strict_types=1);

namespace Site\Registration\Tests\Unit\DomainModel\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\DomainModel\Services\NotificationInterface;
use Shared\DomainModel\ValueObject\Notification;
use Shared\DomainModel\ValueObject\Recipient;
use Site\Registration\DomainModel\Service\PasswordResetNotification;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

#[CoversClass(PasswordResetNotification::class)]
final class PasswordResetNotificationTest extends TestCase
{
    public function testSend(): void
    {
        $notification = $this->createMock(NotificationInterface::class);
        $twig = $this->createMock(TwigEnvironment::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);

        $email = 'test@example.com';
        $name = 'John Doe';
        $token = 'reset-token-123';
        $resetUrl = 'https://example.com/reset-password?token='.$token;
        $emailTitle = 'Reset Password';
        $htmlContent = 'Email content';

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'reset-password',
                ['token' => $token],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($resetUrl);

        $twig->expects($this->once())
            ->method('render')
            ->with(
                '@Registration/email/forgot_password.html.twig',
                [
                    'user_name' => $name,
                    'reset_url' => $resetUrl,
                ]
            )
            ->willReturn($htmlContent);

        $translator->expects($this->once())
            ->method('trans')
            ->with('email.forgot_password.title')
            ->willReturn($emailTitle);

        $notification->expects($this->once())
            ->method('send')
            ->with(
                new Notification($emailTitle, $htmlContent),
                new Recipient($email)
            );

        $service = new PasswordResetNotification(
            $notification,
            $twig,
            $urlGenerator,
            $translator
        );

        $service->send($email, $name, $token);
    }
}
