<?php

declare(strict_types=1);

namespace Site\Profile\Application\Settings\Command;

use Psr\Log\LoggerInterface;
use Site\Profile\DomainModel\Repository\ProfileRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ChangePropertyCommandHandler
{
    public function __construct(
        private ProfileRepositoryInterface $profileRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ChangePropertyCommand $command): void
    {
        try {
            $this->profileRepository->setOrChangeSettingProperty($command->id, $command->property);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}