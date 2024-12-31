<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Console;

use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\Application\UserRegistration\Command\RegisterUserCommand;
use Site\User\DomainModel\Enum\Roles;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:registration:create-user',
    description: 'Run migrations for a specific domain'
)]
final class CreateUserCommand extends Command
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
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->messageBus->dispatch(
                new RegisterUserCommand(
                    'Administrator',
                    Email::fromString($input->getArgument('email')),
                    $input->getArgument('password'),
                    '',
                    new Location(),
                    [Roles::ROLE_ADMIN],
                ),
            );

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return Command::FAILURE;
        }
    }
}
