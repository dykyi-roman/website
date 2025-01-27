<?php

declare(strict_types=1);

namespace Profile\UserStatus\Presentation\Console;

use Profile\UserStatus\Application\GetUserStatus\Service\UserStatusService;
use Profile\UserStatus\Application\UpdateUserStatus\Command\UpdateUserStatusCommand;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Model\UserStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:profile:user-status:sync',
    description: 'Synchronize user online/offline status between Redis and database'
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
                'batch-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of batches to process simultaneously',
                self::BATCH_SIZE,
            );
    }

    /**
     * @throws \InvalidArgumentException When batch size option is invalid
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('<info>Starting synchronization...</info>');

            $batchCount = $this->getValidatedBatchCount($input);

            // Synchronize offline users
            $this->synchronizeOfflineUsers($output, $batchCount);

            // Synchronize online users
            $this->synchronizeOnlineUsers($output, $batchCount);

            $output->writeln('<info>Synchronization completed successfully.</info>');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>Error during synchronization: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * @param int<1, max> $batchCount
     *
     * @throws \Throwable
     */
    private function synchronizeOfflineUsers(OutputInterface $output, int $batchCount): void
    {
        $onlineUsersFromRedis = $this->userStatusService->getAllUserStatuses();
        $onlineUsersInDb = $this->userStatusRepository->findAllOnline();
        $onlineUserIdsFromRedis = array_column($onlineUsersFromRedis, 'userId');

        $output->writeln('<info>Processing offline users...</info>');
        $progressBar = $this->createProgressBar($output, count($onlineUsersInDb));

        $batches = array_chunk($onlineUsersInDb, $batchCount);
        foreach ($batches as $batch) {
            $updates = $this->createOfflineStatusUpdates($batch, $onlineUserIdsFromRedis);
            $progressBar->advance(count($batch));
            if (!empty($updates)) {
                $this->messageBus->dispatch(new UpdateUserStatusCommand($updates));
            }
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param int<1, max> $batchCount
     *
     * @throws \Throwable
     */
    private function synchronizeOnlineUsers(OutputInterface $output, int $batchCount): void
    {
        $onlineUsersFromRedis = $this->userStatusService->getAllUserStatuses();

        $output->writeln('<info>Processing online users...</info>');
        $progressBar = $this->createProgressBar($output, count($onlineUsersFromRedis));

        $batches = array_chunk($onlineUsersFromRedis, $batchCount);
        foreach ($batches as $batch) {
            $updates = $this->createOnlineStatusUpdates($batch);
            $progressBar->advance(count($batch));
            if (!empty($updates)) {
                $this->messageBus->dispatch(new UpdateUserStatusCommand($updates));
            }
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param UserStatus[]                                         $batch
     * @param array<string|\Shared\DomainModel\ValueObject\UserId> $onlineUserIdsFromRedis
     *
     * @return array<array{user_id: string, is_online: bool, last_online_at: string}>
     */
    private function createOfflineStatusUpdates(array $batch, array $onlineUserIdsFromRedis): array
    {
        $updates = [];
        foreach ($batch as $userStatus) {
            if (!in_array($userStatus->getUserId(), $onlineUserIdsFromRedis, true)) {
                $updates[] = [
                    'user_id' => $userStatus->getUserId()->toRfc4122(),
                    'is_online' => false,
                    'last_online_at' => $userStatus->getLastOnlineAt()->format('c'),
                ];
            }
        }

        return $updates;
    }

    /**
     * @param UserUpdateStatus[] $batch
     *
     * @return array<array{user_id: string, is_online: bool, last_online_at: string}>
     */
    private function createOnlineStatusUpdates(array $batch): array
    {
        return array_map(
            static fn (UserUpdateStatus $status): array => [
                'user_id' => $status->userId->toRfc4122(),
                'is_online' => $status->isOnline,
                'last_online_at' => $status->lastOnlineAt->format('c'),
            ],
            $batch
        );
    }

    private function createProgressBar(OutputInterface $output, int $max): ProgressBar
    {
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');

        return $progressBar;
    }

    /**
     * @return int<1, max>
     */
    private function getValidatedBatchCount(InputInterface $input): int
    {
        $value = $input->getOption('batch-count');
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Batch count must be a number');
        }

        $batchCount = (int) $value;
        if ($batchCount < 1) {
            throw new \InvalidArgumentException('Batch count must be at least 1');
        }

        return $batchCount;
    }
}
