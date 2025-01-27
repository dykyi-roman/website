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
    private const int MIN_BATCH_SIZE = 1;

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

    /**
     * @throws \InvalidArgumentException When batch size option is invalid
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('<info>Starting synchronization...</info>');

            $batchSize = $this->getValidatedBatchSize($input);

            // Synchronize offline users
            $this->synchronizeOfflineUsers($output, $batchSize);

            // Synchronize online users
            $this->synchronizeOnlineUsers($output, $batchSize);

            $output->writeln('<info>Synchronization completed successfully.</info>');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>Error during synchronization: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * @param positive-int $batchSize
     *
     * @throws \Throwable
     */
    private function synchronizeOfflineUsers(OutputInterface $output, int $batchSize): void
    {
        $onlineUsersFromRedis = $this->userStatusService->getAllUserStatuses();
        $onlineUsersInDb = $this->userStatusRepository->findAllOnline();
        $onlineUserIdsFromRedis = array_column($onlineUsersFromRedis, 'userId');

        $output->writeln('<info>Processing offline users...</info>');
        $progressBar = $this->createProgressBar($output, count($onlineUsersInDb));

        foreach (array_chunk($onlineUsersInDb, $batchSize) as $batch) {
            $commands = $this->createOfflineStatusCommands($batch, $onlineUserIdsFromRedis);
            if (!empty($commands)) {
                $this->dispatchCommands($commands);
            }
            $progressBar->advance(count($batch));
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param positive-int $batchSize
     *
     * @throws \Throwable
     */
    private function synchronizeOnlineUsers(OutputInterface $output, int $batchSize): void
    {
        $onlineUsersFromRedis = $this->userStatusService->getAllUserStatuses();

        $output->writeln('<info>Processing online users...</info>');
        $progressBar = $this->createProgressBar($output, count($onlineUsersFromRedis));

        foreach (array_chunk($onlineUsersFromRedis, $batchSize) as $batch) {
            /** @var UserUpdateStatus[] $batch */
            $commands = $this->createOnlineStatusCommands($batch);
            if (!empty($commands)) {
                $this->dispatchCommands($commands);
            }
            $progressBar->advance(count($batch));
        }

        $progressBar->finish();
        $output->writeln('');
    }

    /**
     * @param UserStatus[]                                         $batch
     * @param array<string|\Shared\DomainModel\ValueObject\UserId> $onlineUserIdsFromRedis
     *
     * @return UpdateUserStatusCommand[]
     */
    private function createOfflineStatusCommands(array $batch, array $onlineUserIdsFromRedis): array
    {
        $commands = [];
        foreach ($batch as $userStatus) {
            if (!in_array($userStatus->getUserId(), $onlineUserIdsFromRedis, true)) {
                $commands[] = new UpdateUserStatusCommand([
                    'user_id' => $userStatus->getUserId()->toRfc4122(),
                    'is_online' => false,
                    'last_online_at' => $userStatus->getLastOnlineAt()->format('c'),
                ]);
            }
        }

        return $commands;
    }

    /**
     * @param UserUpdateStatus[] $batch
     *
     * @return UpdateUserStatusCommand[]
     */
    private function createOnlineStatusCommands(array $batch): array
    {
        return array_map(
            static fn (UserUpdateStatus $status): UpdateUserStatusCommand => new UpdateUserStatusCommand([
                'user_id' => $status->userId->toRfc4122(),
                'is_online' => $status->isOnline,
                'last_online_at' => $status->lastOnlineAt->format('c'),
            ]),
            $batch
        );
    }

    /**
     * @param UpdateUserStatusCommand[] $commands
     *
     * @throws \Throwable
     */
    private function dispatchCommands(array $commands): void
    {
        $this->messageBus->dispatch(...$commands);
    }

    private function createProgressBar(OutputInterface $output, int $max): ProgressBar
    {
        $progressBar = new ProgressBar($output, $max);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%');

        return $progressBar;
    }

    /**
     * @return positive-int
     *
     * @throws \InvalidArgumentException When batch size is invalid
     */
    private function getValidatedBatchSize(InputInterface $input): int
    {
        $value = $input->getOption('batch-size');
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Batch size must be a number');
        }

        $batchSize = (int) $value;
        if ($batchSize < self::MIN_BATCH_SIZE) {
            throw new \InvalidArgumentException(sprintf('Batch size must be at least %d', self::MIN_BATCH_SIZE));
        }

        /* @var positive-int */
        return min($batchSize, self::BATCH_SIZE);
    }
}
