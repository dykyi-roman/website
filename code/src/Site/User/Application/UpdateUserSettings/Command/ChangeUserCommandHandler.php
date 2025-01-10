<?php

declare(strict_types=1);

namespace Site\User\Application\UpdateUserSettings\Command;

use Shared\DomainModel\ValueObject\Email;
use Site\User\DomainModel\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ChangeUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(ChangeUserCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        $user->changeName($command->name);
        $user->changeEmail(Email::fromString($command->email));
        $user->changePhone($command->phone);
        
        if ($command->avatar !== null) {
            $user->changeAvatar($command->avatar);
        }

        $this->userRepository->save($user);
    }
}
