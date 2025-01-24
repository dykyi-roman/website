<?php

declare(strict_types=1);

namespace Notifications\Presentation\Console;

use Notifications\Application\CreateUserNotification\Command\CreateUserNotificationCommand;
use Notifications\DomainModel\Enum\NotificationName;
use Notifications\DomainModel\Enum\NotificationType;
use Notifications\DomainModel\ValueObject\TranslatableText;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\UserId;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:notification:send:one',
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
            ->addArgument('user-id', InputArgument::REQUIRED, 'User ID')
            ->addArgument('notification-name', InputArgument::REQUIRED, 'Notification name')
            ->addArgument('notification-type', InputArgument::REQUIRED, 'Notification type')
            ->addArgument('notification-title', InputArgument::REQUIRED, 'Notification title')
            ->addArgument('notification-message', InputArgument::REQUIRED, 'Notification message');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $userId = $this->getInputValue($input, 'user-id');

            $this->messageBus->dispatch(
                new CreateUserNotificationCommand(
                    UserId::fromString($userId),
                    NotificationName::from($this->getInputValue($input, 'notification-name')),
                    NotificationType::from($this->getInputValue($input, 'notification-type')),
                    TranslatableText::create($this->getInputValue($input, 'notification-title')),
                    TranslatableText::create($this->getInputValue($input, 'notification-message')),
                ),
            );

            $output->writeln(sprintf('Send 1 notifications to %s', $userId));

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            $output->writeln(sprintf('Error: %s', $exception->getMessage()));

            return Command::FAILURE;
        }
    }

    private function getInputValue(InputInterface $input, string $name): string
    {
        $value = $input->getArgument($name);
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be a string', $name));
        }

        return $value;
    }
}
