<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Service;

use Profile\User\DomainModel\Model\UserInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserPasswordHasher implements PasswordChangeServiceInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function isValid(UserInterface $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }

    public function change(UserInterface $user, string $password): void
    {
        try {
            $user->updatePassword($this->passwordHasher->hashPassword($user, $password));
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error('Password change failed', [
                'userId' => $user->id()->toRfc4122(),
                'password' => $password,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
