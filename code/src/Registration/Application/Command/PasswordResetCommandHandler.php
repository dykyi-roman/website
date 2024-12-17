<?php

declare(strict_types=1);

namespace App\Registration\Application\Command;

use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Email;
use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\Recipient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

#[AsMessageHandler]
final readonly class PasswordResetCommandHandler
{
    public function __construct(
        private NotificationInterface $notification,
        private UserRepositoryInterface $userRepository,
        private TwigEnvironment $twig,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private TokenGeneratorInterface $tokenGenerator,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(PasswordResetCommand $command): void
    {
        $user = $this->userRepository->findByEmail(Email::fromString($command->email));
        if (!$user) {
            return;
        }

        $token = $this->tokenGenerator->generate($user->getId()->toBase32());
        $user->setToken($token);
        $this->userRepository->save($user);

        try {
            $resetPasswordUrl = $this->urlGenerator->generate('reset-password', [
                'token' => $token,
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $htmlContent = $this->twig->render('@Registration/email/forgot_password.html.twig', [
                'reset_url' => $resetPasswordUrl
            ]);

            $this->notification->send(
                new Notification(
                    $this->translator->trans('email.forgot_password.title'),
                    $htmlContent
                ),
                new Recipient($command->email)
            );
        } catch (\Throwable $exception) {
            $this->logger->error('Password reset failed', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
