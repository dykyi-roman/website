<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\TokenGeneratorInterface;
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
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator,
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
            'isValid' => $this->tokenGenerator->isValid($request->token),
        ]);
    }

    #[Route('/reset-password', name: 'reset-password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequestDTO $request,
        UserRepositoryInterface $userRepository,
        ResetPasswordJsonResponder $responder
    ): ResetPasswordJsonResponder {
        try {
            // Validate reset token
            $this->validateResetToken($request->token);

            // Find user by reset token
            $user = $this->userRepository->findByResetToken($request->token);
            if (!$user) {
                throw new \InvalidArgumentException(
                    $this->translator->trans('Invalid or expired reset token')
                );
            }

            // Validate password complexity
            $this->validatePasswordComplexity($request->password);

            // Reset password using domain service
            $this->passwordResetService->resetPassword(
                $user, 
                $request->password
            );

            // Log successful password reset
            $this->logger->info('Password reset successful', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]);

            return $responder->success($this->translator->trans('Password successfully changed'))->respond();
        } catch (\InvalidArgumentException $exception) {
            // Log validation errors
            $this->logger->warning('Password reset validation failed', [
                'error' => $exception->getMessage()
            ]);

            return $responder->error(
                $exception->getMessage(), 
                422 // Unprocessable Entity
            )->respond();
        } catch (\Exception $exception) {
            // Log unexpected errors
            $this->logger->error('Unexpected error during password reset', [
                'error' => $exception->getMessage()
            ]);

            return $responder->error(
                $this->translator->trans('An unexpected error occurred'), 
                500
            )->respond();
        }
    }

    private function validateResetToken(string $token): void
    {
        // Implement reset token validation logic here
    }

    private function validatePasswordComplexity(string $password): void
    {
        // Implement password complexity validation logic here
    }
}
