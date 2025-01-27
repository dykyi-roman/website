<?php

declare(strict_types=1);

namespace Profile\UserStatus\Presentation\Console;

use Profile\UserStatus\Application\GetUserStatus\Service\UserStatusService;
use Profile\UserStatus\Application\UpdateUserStatus\Command\UpdateUserStatusCommand;
use Profile\UserStatus\DomainModel\Dto\UserUpdateStatus;
use Profile\UserStatus\DomainModel\Repository\UserStatusRepositoryInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $batchSize = $this->getIntOption($input, 'batch-size');

        $onlineUserIdsFromRedis = array_column($onlineUsersFromRedis, 'userId');
        $validBatchSize = max(1, min($batchSize, self::BATCH_SIZE));
        foreach (array_chunk($onlineUsersInDb, $validBatchSize) as $batch) {
            /** @var array<string, array<string, mixed>> $updateItems */
            $updateItems = [];
            foreach ($batch as $userStatus) {
                if (!in_array($userStatus->getUserId(), $onlineUserIdsFromRedis, true)) {
                    $updateItems[] = [
                        'user_id' => $userStatus->getUserId()->toRfc4122(),
                        'is_online' => false,
                        'last_online_at' => $userStatus->getLastOnlineAt()->format('c'),
                    ];
                }
            }

            if (!empty($updateItems)) {
                $this->messageBus->dispatch(new UpdateUserStatusCommand($updateItems));
            }
        }

        /** @var UserUpdateStatus[] $batch */
        foreach (array_chunk($onlineUsersFromRedis, $validBatchSize) as $batch) {
            /** @var array<string, array<string, mixed>> $updateItems */
            $updateItems = [];
            foreach ($batch as $status) {
                $updateItems[] = [
                    'user_id' => $status->userId->toRfc4122(),
                    'is_online' => $status->isOnline,
                    'last_online_at' => $status->lastOnlineAt->format('c'),
                ];
            }

            $this->messageBus->dispatch(new UpdateUserStatusCommand($updateItems));

            $progress = round((count($batch) / count($onlineUsersFromRedis)) * 100, 2);
            $output->writeln(sprintf('<info>Progress: %s%%</info>', $progress));
        }

        return Command::SUCCESS;
    }

    private function getIntOption(InputInterface $input, string $name): int
    {
        $value = $input->getOption($name);
        if (!is_int($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be a int', $name));
        }

        return $value;
    }
}
