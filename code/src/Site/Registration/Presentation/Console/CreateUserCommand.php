<?php

declare(strict_types=1);

namespace Site\Registration\Presentation\Console;

use Profile\User\DomainModel\Enum\Roles;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Services\MessageBusInterface;
use Shared\DomainModel\ValueObject\Email;
use Shared\DomainModel\ValueObject\Location;
use Site\Registration\Application\UserRegistration\Command\RegisterUserCommand;
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
            $email = $input->getArgument('email');
            if (!is_string($email)) {
                throw new \InvalidArgumentException('Email must be a string');
            }

            $password = $input->getArgument('password');
            if (!is_string($password)) {
                throw new \InvalidArgumentException('Password must be a string');
            }

            $this->messageBus->dispatch(
                new RegisterUserCommand(
                    'Administrator',
                    Email::fromString($email),
                    $password,
                    '',
                    new Location(),
                    [Roles::ROLE_ADMIN->value],
                ),
            );

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
            ]);

            return Command::FAILURE;
        }
    }
}
