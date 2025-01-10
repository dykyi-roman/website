<?php

declare(strict_types=1);

namespace Site\Registration\Application\ForgontPassword\Command;

use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\Email;
use Site\Registration\DomainModel\Service\PasswordResetNotification;
use Site\Registration\DomainModel\Service\TokenGeneratorInterface;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ForgotPasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator,
        private PasswordResetNotification $passwordResetNotification,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ForgotPasswordCommand $command): void
    {
        $user = $this->userRepository->findByEmail(Email::fromString($command->email));
        if (!$user || $user->isDeleted() || !$user->isActive()) {
            return;
        }

        try {
            $token = $this->tokenGenerator->generate($user->id()->toBase32());
            $user->setPasswordToken($token);
            $this->userRepository->save($user);

            $this->passwordResetNotification->send($command->email, $user->name(), $token);
        } catch (\Throwable $exception) {
            $this->logger->error('Password reset failed', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
