<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsMessageHandler]
final readonly class DeleteUserAccountCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenStorageInterface $tokenStorage,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(DeleteUserAccountCommand $command): void
    {
        try {
            $user = $this->userRepository->findById($command->userId);
            $user->delete();
            $this->userRepository->save($user);

            $this->tokenStorage->setToken(null);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }
}
