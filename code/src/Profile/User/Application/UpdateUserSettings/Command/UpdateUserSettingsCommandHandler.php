<?php

declare(strict_types=1);

namespace Profile\User\Application\UpdateUserSettings\Command;

use Profile\User\DomainModel\Exception\UserExistException;
use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Shared\DomainModel\ValueObject\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserSettingsCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(UpdateUserSettingsCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        $newEmail = Email::fromString($command->email);

        if ($this->userRepository->findByEmail($newEmail) && !$user->email()->equals($newEmail)) {
            throw new UserExistException($user->id());
        }

        $user->changeName($command->name);
        $user->changeEmail($newEmail);
        $user->changePhone($command->phone);

        if (null !== $command->avatar) {
            $user->changeAvatar($command->avatar);
        }

        $this->userRepository->save($user);
    }
}
