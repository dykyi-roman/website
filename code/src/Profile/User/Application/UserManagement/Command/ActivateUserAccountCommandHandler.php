<?php

declare(strict_types=1);

namespace Profile\User\Application\UserManagement\Command;

use Profile\User\DomainModel\Service\UserPrivacyServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ActivateUserAccountCommandHandler
{
    public function __construct(
        private UserPrivacyServiceInterface $userPrivacyService,
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(ActivateUserAccountCommand $command): void
    {
        if ($command->userStatus->isActive()) {
            $this->userPrivacyService->activate($command->userId);
        } else {
            $this->userPrivacyService->deactivate($command->userId);
        }
    }
}
