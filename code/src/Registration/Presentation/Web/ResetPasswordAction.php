<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\Presentation\Web\Request\ResetPasswordFormRequestDTO;
use App\Registration\Presentation\Web\Request\ResetPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ResetPasswordHtmlResponder;
use App\Registration\Presentation\Web\Response\ResetPasswordJsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ResetPasswordAction
{
    public function __construct(
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    #[Route('/reset-password', name: 'reset-password-page', methods: ['GET'])]
    public function showResetPasswordPage(
        #[MapQueryString] ResetPasswordFormRequestDTO $request,
        ResetPasswordHtmlResponder $responder,
    ): ResetPasswordHtmlResponder {
        return $responder->respond([
            'page_title' => $this->translator->trans('Reset Password'),
            'token' => $request->token,
        ]);
    }

    #[Route('/reset-password', name: 'reset-password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequestDTO $request,
        ResetPasswordJsonResponder $responder
    ): ResetPasswordJsonResponder {
        try {
            // TODO: Implement actual password reset logic
            // This might involve:
            // 1. Verifying reset token
            // 2. Finding user by token or email
            // 3. Hashing new password
            // 4. Updating user's password in database
            // 5. Invalidating reset token

            return $responder->success($this->translator->trans('Password is changed!'))->respond();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $responder->validationError(
                $this->translator->trans('An error occurred while resetting password'),
                'password'
            )->respond();
        }
    }
}
