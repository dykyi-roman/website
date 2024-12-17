<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\DomainModel\Services\NotificationInterface;
use App\Shared\DomainModel\ValueObject\Notification;
use App\Shared\Infrastructure\Notification\Recipient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private TwigEnvironment $twig,
    ) {
    }

    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        NotificationInterface $notification,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger,
    ): ForgotPasswordJsonResponder {
        try {
            $resetPasswordUrl = $urlGenerator->generate('reset-password', [
                'token' => $this->tokenGenerator->generateToken(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $htmlContent = $this->twig->render('@Registration/email/forgot_password.html.twig', [
                'reset_url' => $resetPasswordUrl
            ]);

            $notification->send(
                new Notification(
                    $translator->trans('email.forgot_password.title'),
                    $htmlContent,
                ),
                new Recipient((string)$request->email())
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            $logger->error($exception->getMessage());

            return $responder->error($exception)->respond();
        }
    }
}
