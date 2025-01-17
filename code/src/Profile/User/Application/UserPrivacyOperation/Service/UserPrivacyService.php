<?php

declare(strict_types=1);

namespace Profile\User\Application\UserPrivacyOperation\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\ActivateUserException;
use Profile\User\DomainModel\Exception\DeactivateUserException;
use Profile\User\DomainModel\Exception\DeleteUserException;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;

final readonly class UserPrivacyService implements UserPrivacyServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws DeleteUserException
     */
    public function delete(UserId $userId): void
    {
        try {
            $user = $this->userRepository->findById($userId);
            $user->delete();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw new DeleteUserException($userId);
        }
    }

    /**
     * @throws ActivateUserException
     */
    public function activate(UserId $userId): void
    {
        try {
            $user = $this->userRepository->findById($userId);
            $user->activate();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw new ActivateUserException($userId);
        }
    }

    /**
     * @throws DeactivateUserException
     */
    public function deactivate(UserId $userId): void
    {
        try {
            $user = $this->userRepository->findById($userId);
            $user->deactivate();
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            throw new DeactivateUserException($userId);
        }
    }
}
