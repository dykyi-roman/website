<?php

declare(strict_types=1);

namespace Profile\UserStatus\Presentation\Console;

use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Profile\UserStatus\DomainModel\Service\UserStatusCache;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SynchronizeUserStatusCommand extends Command
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private readonly UserStatusCache $userStatusCache,
        private readonly MessageBusInterface $messageBus,
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

        $userStatuses = $this->userStatusCache->getAllUserStatuses();
        $totalStatuses = count($userStatuses);
        $processedStatuses = 0;

        foreach (array_chunk($userStatuses, self::BATCH_SIZE) as $batchStatuses) {
            foreach ($batchStatuses as $userStatus) {
                // ...
                $processedStatuses++;
            }
            
            $progress = round(($processedStatuses / $totalStatuses) * 100, 2);
            $output->writeln(sprintf('<info>Progress: %s%% (%d/%d)</info>', $progress, $processedStatuses, $totalStatuses));
        }

        $output->writeln('<info>Synchronization complete!</info>');

        return Command::SUCCESS;
    }
}