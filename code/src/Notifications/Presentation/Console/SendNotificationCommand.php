<?php

declare(strict_types=1);

namespace Notifications\Presentation\Console;

use Notifications\Application\CreateNotification\Command\CreateNotificationMessageCommand;
use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Profile\User\Application\Notifications\Query\UsersNotificationQuery;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;
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
            $notificationId = $input->getArgument('notification-id');
            if (!is_string($notificationId)) {
                throw new \InvalidArgumentException('Notification ID must be a string');
            }

            $userIdOption = $input->getOption('user-id');
            if (null !== $userIdOption && '' !== $userIdOption) {
                if (!is_string($userIdOption)) {
                    throw new \InvalidArgumentException('User ID must be a string');
                }

                $this->messageBus->dispatch(
                    new CreateNotificationMessageCommand(
                        UserId::fromString($userIdOption),
                        NotificationName::HAPPY_BIRTHDAY,
                        NotificationType::PERSONAL,
                        TranslatableText::create('notifications.notification.happy-birthday.title'),
                        TranslatableText::create('notifications.notification.happy-birthday.message', ['%name%' => 'Roman']),
                    ),
                );

                $output->writeln(sprintf('Send 1 notifications to %s', $userIdOption));

                return Command::SUCCESS;
            }

            /** @var array<UserId> $userIds */
            $userIds = $this->messageBus->dispatch(
                new UsersNotificationQuery(),
            );
            foreach ($userIds as $userId) {
                $this->messageBus->dispatch(
                    new CreateNotificationMessageCommand(
                        $userId,
                        NotificationName::HAPPY_BIRTHDAY,
                        NotificationType::PERSONAL,
                        TranslatableText::create('notifications.notification.happy-birthday.title'),
                        TranslatableText::create('notifications.notification.happy-birthday.message', ['name' => 'Roman']),
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
