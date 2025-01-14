<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Web;

use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Site\Registration\Application\ForgontPassword\Command\ForgotPasswordCommand;
use Site\Registration\Application\ForgontPassword\Service\PasswordResetRateLimiterService;
use Site\Registration\Presentation\Web\Request\ForgotPasswordRequestDto;
use Site\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
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
        #[MapRequestPayload] ForgotPasswordRequestDto $request,
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

            return $responder->success($translator->trans('letter_sent_success'))->respond();
        } catch (\Throwable $exception) {
            $logger->error('Password send reset email failed', [
                'email' => $request->email()->value,
                'error' => $exception->getMessage(),
            ]);

            return $responder->validationError(
                $translator->trans($exception->getMessage())
            )->respond();
        }
    }
}
