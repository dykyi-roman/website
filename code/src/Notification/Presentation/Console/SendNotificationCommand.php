<?php

declare(strict_types=1);

namespace Notification\Presentation\Console;

use Notification\Application\CreateNotification\Command\CreateNotificationMessageCommand;
use Notification\DomainModel\Enum\NotificationId;
use Profile\User\Application\FindUsersForNotifications\Query\UsersNotificationQuery;
use Profile\User\DomainModel\Enum\UserId;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:notification:send',
    description: 'Send notifications'
)]
final class SendNotificationCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('notification-id', InputArgument::REQUIRED, 'Notification ID')
            ->addOption('user-id', 'user-id', InputOption::VALUE_OPTIONAL, 'User ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($userId = $input->getOption('user-id')) {
                $this->messageBus->dispatch(
                    new CreateNotificationMessageCommand(
                        NotificationId::from($input->getArgument('notification-id')),
                        UserId::fromString($userId),
                    ),
                );

                $output->writeln(sprintf('Send 1 notifications to %s', $userId));

                return Command::SUCCESS;
            }

            /** @var array<UserId> $userIds */
            $userIds = $this->messageBus->dispatch(
                new UsersNotificationQuery(),
            );
            foreach ($userIds as $userId) {
                $this->messageBus->dispatch(
                    new CreateNotificationMessageCommand(
                        NotificationId::from($input->getArgument('notification-id')),
                        $userId,
                    ),
                );
            }

            $output->writeln(sprintf('Send %d notifications', count($userIds)));

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
            ]);

            $output->writeln(sprintf('Error: %s', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
