<?php

declare(strict_types=1);

namespace Shared\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:migrations:create-domain',
    description: 'Create a migration for a specific domain'
)]
final class CreateDomainMigrationCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain path (e.g., site/user or shared)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domainPath = strtolower($input->getArgument('domain'));
        $parts = explode('/', $domainPath);

        // Handle both formats: "site/user" and "shared"
        $fullPath = count($parts) > 1
            ? sprintf('%s/src/%s/%s', $this->projectDir, ucfirst($parts[0]), ucfirst($parts[1]))
            : sprintf('%s/src/%s', $this->projectDir, ucfirst($parts[0]));

        if (!is_dir($fullPath)) {
            $output->writeln(sprintf('<error>Domain directory "%s" not found.</error>', $fullPath));

            return Command::FAILURE;
        }

        $configPath = sprintf('%s/Resources/Config/migrations.yaml', $fullPath);
        if (!file_exists($configPath)) {
            $output->writeln(sprintf('<error>Migration config not found at: %s</error>', $configPath));

            return Command::FAILURE;
        }

        $application = $this->getApplication();
        if (!$application) {
            $output->writeln('<error>Could not get application instance.</error>');

            return Command::FAILURE;
        }

        try {
            $command = $application->find('doctrine:migrations:diff');
            $arguments = [
                'command' => 'doctrine:migrations:diff',
                '--configuration' => $configPath,
                '--no-interaction' => true,
            ];

            $diffInput = new ArrayInput($arguments);

            return $command->run($diffInput, $output);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error creating migration: %s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
