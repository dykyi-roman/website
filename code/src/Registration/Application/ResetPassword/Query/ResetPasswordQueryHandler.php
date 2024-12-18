<?php

declare(strict_types=1);

namespace App\Registration\Application\ResetPassword\Query;

use App\Registration\Application\ResetPassword\ValueObject\ResetPasswordResponse;
use App\Registration\DomainModel\Exception\InvalidPasswordException;
use App\Registration\DomainModel\Exception\PasswordIsNotMatchException;
use App\Registration\DomainModel\Exception\TokenExpiredException;
use App\Registration\DomainModel\Service\ResetPasswordService;
use Psr\Log\LoggerInterface;
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
                message: $this->translator->trans('Password successfully reset')
            );
        } catch (TokenExpiredException $exception) {
            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('Reset token is invalid or has expired'),
                errors: ['token' => $exception->getMessage()]
            );
        } catch (InvalidPasswordException $exception) {
            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('Invalid password'),
                errors: ['password' => $exception->getMessage()]
            );
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return new ResetPasswordResponse(
                success: false,
                message: $this->translator->trans('An unexpected error occurred'),
                errors: ['general' => $exception->getMessage()]
            );
        }
    }
}
