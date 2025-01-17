<?php

declare(strict_types=1);

namespace Profile\Setting\Application\SettingsPrivacy\Command;

use Profile\User\Application\UserPrivacyOperation\Service\UserPrivacyServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsMessageHandler]
final readonly class DeleteUserAccountCommandHandler
{
    public function __construct(
        private UserPrivacyServiceInterface $userPrivacyService,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @throws \Profile\User\DomainModel\Exception\DeleteUserException
     */
    public function __invoke(DeleteUserAccountCommand $command): void
    {
        $this->userPrivacyService->delete($command->userId);
        $this->tokenStorage->setToken(null);
    }
}
