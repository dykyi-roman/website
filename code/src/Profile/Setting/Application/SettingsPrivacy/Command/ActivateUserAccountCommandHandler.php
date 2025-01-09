<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

use Psr\Log\LoggerInterface;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Site\User\DomainModel\Service\UserFetcher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivateUserAccountCommandHandler
{
    public function __construct(
        private UserFetcher $userFetcher,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(ActivateUserAccountCommand $command): void
    {
        try {
            $user = $this->userFetcher->fetch();
            $command->userStatus ? $user->activate() : $user->deactivate();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }
}
