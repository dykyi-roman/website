<?php

declare(strict_types=1);

namespace Profile\UserStatus\Presentation\Console;

use Profile\UserStatus\Application\GetUserStatus\Service\UserStatusService;
use Profile\UserStatus\Application\UpdateUserStatus\Command\UpdateUserStatusCommand;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:profile:user-status:sync',
    description: 'Create a migration for a specific domain'
)]
final class SynchronizeUserStatusCommand extends Command
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private readonly UserStatusService $userStatusService,
        private readonly UserStatusRepositoryInterface $userStatusRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronizes user online/offline status from Redis to the database.')
            ->setHelp('This command retrieves user statuses from Redis and updates them in the database.')
            ->addOption(
                'batch-size',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Number of records to process in one batch',
                self::BATCH_SIZE,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting synchronization...</info>');

        $onlineUsersFromRedis = $this->userStatusService->getAllUserStatuses();
        $onlineUsersInDb = $this->userStatusRepository->findAllOnline();

        $batchSize = (int) $input->getOption('batch-size');

        $onlineUserIdsFromRedis = array_column($onlineUsersFromRedis, 'userId');
        foreach ($onlineUsersInDb as $userStatus) {
            if (!in_array($userStatus->getUserId(), $onlineUserIdsFromRedis, true)) {
                $this->messageBus->dispatch(
                    new UpdateUserStatusCommand([
                        [
                            'user_id' => $userStatus->getUserId(),
                            'is_online' => false,
                            'last_online_at' => $userStatus->getLastOnlineAt(),
                        ],
                    ])
                );
            }
        }

        /** @var UserUpdateStatus[] $batch */
        foreach (array_chunk($onlineUsersFromRedis, $batchSize) as $batch) {
            $updateItems = array_map(static fn (UserUpdateStatus $status) => $status->jsonSerialize(), $batch);
            $this->messageBus->dispatch(new UpdateUserStatusCommand($updateItems));

            $progress = round((count($batch) / count($onlineUsersFromRedis)) * 100, 2);
            $output->writeln(sprintf('<info>Progress: %s%%</info>', $progress));
        }

        return Command::SUCCESS;
    }
}
