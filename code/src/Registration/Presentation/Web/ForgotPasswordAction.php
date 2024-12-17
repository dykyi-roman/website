<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\DomainModel\Service\PasswordResetService;
use App\Registration\DomainModel\ValueObject\ResetPasswordToken;
use App\Registration\Presentation\Web\Request\ForgotPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ForgotPasswordJsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ForgotPasswordAction
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private PasswordResetService $passwordResetService,
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
            $this->passwordResetService->passwordReset(
                $request->email(),
                new ResetPasswordToken($this->tokenGenerator),
            );

            return $responder->success($translator->trans('Letter sent. Check your email.'))->respond();
        } catch (\Throwable $exception) {
            $logger->error('Password reset failed', [
                'email' => $request->email(),
                'error' => $exception->getMessage(),
            ]);

            return $responder->error($exception)->respond();
        }
    }
}
