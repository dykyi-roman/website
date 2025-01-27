<?php

declare(strict_types=1);

namespace Profile\UserStatus\Presentation\Console;

use Profile\User\DomainModel\Repository\UserRepositoryInterface;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Profile\UserStatus\DomainModel\Service\UserStatusCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SynchronizeUserStatusCommand extends Command
{
    public function __construct(
        private readonly UserStatusCache $userStatusCache,
        private readonly UserStatusRepositoryInterface $userStatusRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronizes user online/offline status from Redis to the database.')
            ->setHelp('This command retrieves user statuses from Redis and updates them in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting synchronization...</info>');

        $keys = $this->userStatusCache->getAllKeys();

        foreach ($keys as $key) {
            $userId = $this->extractUserIdFromKey($key);
        }

        $output->writeln('<info>Synchronization complete!</info>');

        return Command::SUCCESS;
    }

    private function extractUserIdFromKey(string $key): int
    {
        return (int)str_replace('user:status:', '', $key);
    }
}