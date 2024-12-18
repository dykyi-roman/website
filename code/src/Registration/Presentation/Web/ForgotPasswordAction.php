<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Application\ForgontPassword\Command\ForgotPasswordCommand;
use App\Registration\DomainModel\Service\PasswordResetRateLimiterService;
use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use App\Shared\DomainModel\Services\MessageBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private PasswordResetRateLimiterService $rateLimiterService,
    ) {
    }

    #[Route('/forgot-password', name: 'forgot-password', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ForgotPasswordRequestDTO $request,
        ForgotPasswordJsonResponder $responder,
        TranslatorInterface $translator,
        LoggerInterface $logger,
    ): ForgotPasswordJsonResponder {
        try {
            $email = $request->email();
            $this->rateLimiterService->tryPasswordResetEmail(
                $email,
                function () use ($email) {
                    $this->messageBus->dispatch(
                        new ForgotPasswordCommand($email->value)
                    );
                }
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            $logger->error('Password send reset email failed', [
                'email' => $request->email()->value,
                'error' => $exception->getMessage(),
            ]);

            return $responder->validationError(
                $translator->trans('Unexpected error during restore password')
            )->respond();
        }
    }
}
