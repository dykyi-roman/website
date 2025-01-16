<?php

declare(strict_types=1);

namespace Profile\User\Application\UpdateUserSettings\Command;

use Profile\User\DomainModel\Service\UserServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserSettingsCommandHandler
{
    public function __construct(
        private UserServiceInterface $userService,
    ) {
    }

    public function __invoke(UpdateUserSettingsCommand $command): void
    {
        $this->userService->update(
            $command->userId,
            $command->name,
            $command->email,
            $command->phone,
            $command->avatar,
        );
    }
}
