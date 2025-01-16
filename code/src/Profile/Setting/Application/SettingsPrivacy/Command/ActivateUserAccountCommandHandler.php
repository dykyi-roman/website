<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

use Profile\User\DomainModel\Service\UserServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivateUserAccountCommandHandler
{
    public function __construct(
        private UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(ActivateUserAccountCommand $command): void
    {
        if ($command->userStatus) {
            $this->userService->activate($command->userId);
        } else {
            $this->userService->deactivate($command->userId);
        }
    }
}
