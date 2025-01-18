<?php

declare(strict_types=1);

namespace Profile\User\Application\UserAuthentication\Command;

use Profile\User\Application\UserManagement\Service\PasswordChangeServiceInterface;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateUserPasswordCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordChangeServiceInterface $passwordHasher,
    ) {
    }

    public function __invoke(CreateUserPasswordCommand $command): void
    {
        if ($command->isEqual()) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        $user = $this->userRepository->findById($command->userId);
        $this->passwordHasher->change($user, $command->password);
    }
}