<?php

declare(strict_types=1);

namespace App\Registration\Presentation\Web;

use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\PasswordResetService;
use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use App\Registration\Presentation\Web\Request\ResetPasswordFormRequestDTO;
use App\Registration\Presentation\Web\Request\ResetPasswordRequestDTO;
use App\Registration\Presentation\Web\Response\ResetPasswordHtmlResponder;
use App\Registration\Presentation\Web\Response\ResetPasswordJsonResponder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ResetPasswordAction
{
    public function __construct(
        private TranslatorInterface $translator,
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
        LoggerInterface $logger,
        UserPasswordHasherInterface $passwordHasher,
        ResetPasswordJsonResponder $responder
    ): ResetPasswordJsonResponder {
        try {
            if (!$this->tokenGenerator->isValid($request->token)) {
                throw new \InvalidArgumentException('Token is not valid.');
            }

            /** @var \App\Client\DomainModel\Model\Client $user */
            $user = $userRepository->findByToken($request->token);
            if (!$user) {
                throw new \InvalidArgumentException('Invalid or expired reset token');
            }

            if ($request->password !== $request->confirmPassword) {
                throw new \InvalidArgumentException('Passwords do not match');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $request->password));
            $user->setToken(null);
            $userRepository->save($user);

            $logger->info('Password reset successful', [
                'user_id' => (string) $user->getId(),
                'email' => (string) $user->getEmail(),
            ]);

            return $responder->success($this->translator->trans('Password is changed!'))->respond();
        } catch (\InvalidArgumentException $exception) {
            $logger->warning('Password reset validation failed', [
                'error' => $exception->getMessage(),
                'password',
            ]);

            return $responder->validationError($this->translator->trans('Password reset validation failed'))->respond();
        } catch (\Exception $exception) {
            $logger->error('Unexpected error during password reset', [
                'error' => $exception->getMessage(),
            ]);

            return $responder->validationError(
                $this->translator->trans('Unexpected error during password reset'),
            )->respond();
        }
    }
}
