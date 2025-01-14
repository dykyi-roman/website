<?php

declare(strict_types=1);

namespace Profile\User\Application\VerifyUserProfile\Command;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class VerifyUserEmailCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(VerifyUserEmailCommand $command): void
    {
        try {
            $user = $this->userRepository->findById($command->userId);
            $user->verifyEmail();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }
}
