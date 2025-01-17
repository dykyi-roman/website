<?php

declare(strict_types=1);

namespace Profile\User\Application\ChangePassword\Command;

use Profile\User\Application\ChangePassword\Service\PasswordChangeServiceInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ChangePasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordChangeServiceInterface $passwordHasher,
    ) {
    }

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        if (!$this->passwordHasher->isValid($user, $command->currentPassword)) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        $this->passwordHasher->change($user, $command->newPassword);
    }
}
