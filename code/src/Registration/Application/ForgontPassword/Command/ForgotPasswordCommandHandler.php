<?php

declare(strict_types=1);

namespace App\Registration\Application\ForgontPassword\Command;

use App\Registration\DomainModel\Repository\UserRepositoryInterface;
use App\Registration\DomainModel\Service\PasswordResetNotification;
use App\Registration\DomainModel\Service\TokenGeneratorInterface;
use App\Shared\DomainModel\ValueObject\Email;
use Psr\Log\LoggerInterface;
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
        if (!$user) {
            return;
        }

        try {
            $token = $this->tokenGenerator->generate($user->getId()->toBase32());
            $user->setToken($token);
            $this->userRepository->save($user);

            $this->passwordResetNotification->send($command->email, $user->getName(), $token);
        } catch (\Throwable $exception) {
            $this->logger->error('Password reset failed', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
