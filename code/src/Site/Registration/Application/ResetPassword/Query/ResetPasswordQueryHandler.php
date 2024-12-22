<?php

declare(strict_types=1);

namespace Site\Registration\Application\ResetPassword\Query;

use Psr\Log\LoggerInterface;
use Site\Registration\Application\ResetPassword\ValueObject\ResetPasswordResponse;
use Site\Registration\DomainModel\Exception\InvalidPasswordException;
use Site\Registration\DomainModel\Exception\PasswordIsNotMatchException;
use Site\Registration\DomainModel\Exception\TokenExpiredException;
use Site\Registration\DomainModel\Service\ResetPasswordService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class ResetPasswordQueryHandler
{
    public function __construct(
        private ResetPasswordService $resetPassword,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ResetPasswordQuery $query): ResetPasswordResponse
    {
        try {
            if ($query->password !== $query->confirmPassword) {
                throw new PasswordIsNotMatchException();
            }

            $this->resetPassword->reset($query->token, $query->password);

            return new ResetPasswordResponse(
                success: true,
                message: $this->translator->trans('password_reset_success')
            );
        } catch (TokenExpiredException $exception) {
            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('reset_token_expired'),
                errors: ['token' => $exception->getMessage()]
            );
        } catch (InvalidPasswordException $exception) {
            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('invalid_password'),
                errors: ['password' => $exception->getMessage()]
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('unexpected_reset_error'),
                errors: ['general' => $exception->getMessage()]
            );
        }
    }
}
