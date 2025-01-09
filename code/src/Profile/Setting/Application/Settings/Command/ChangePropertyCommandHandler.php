<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Command;

use Profile\Setting\DomainModel\Repository\SettingRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ChangePropertyCommandHandler
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ChangePropertyCommand $command): void
    {
        try {
            $this->settingRepository->updateProperties($command->id, ...$command->properties);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
