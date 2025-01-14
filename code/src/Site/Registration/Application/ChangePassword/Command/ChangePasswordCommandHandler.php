<?php

declare(strict_types=1);

namespace Site\Registration\Application\ChangePassword\Command;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class ChangePasswordCommandHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ChangePasswordCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        if (!$this->passwordHasher->isPasswordValid($user, $command->currentPassword)) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        try {
            $user->updatePassword($this->passwordHasher->hashPassword($user, $command->newPassword));
            $this->userRepository->save($user);
        } catch (\Throwable $exception) {
            $this->logger->error('Password change failed', [
                'userId' => $command->userId->toRfc4122(),
                'newPassword' => $command->newPassword,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
