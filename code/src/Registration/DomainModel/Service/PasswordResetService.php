<?php

declare(strict_types=1);

namespace App\Registration\DomainModel\Service;

use App\Registration\DomainModel\ValueObject\ResetPasswordToken;
use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Email;
use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

final readonly class PasswordResetService
{
    public function __construct(
        private NotificationInterface $notification,
        private TwigEnvironment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function passwordReset(Email $email, ResetPasswordToken $token): void
    {
        $resetPasswordUrl = $this->urlGenerator->generate('reset-password', [
            'token' => (string) $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $htmlContent = $this->twig->render('@Registration/email/forgot_password.html.twig', [
            'reset_url' => $resetPasswordUrl
        ]);

        $this->notification->send(
            new Notification(
                $this->translator->trans('email.forgot_password.title'),
                $htmlContent
            ),
            new Recipient((string) $email)
        );
    }
}
