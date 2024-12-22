<?php

declare(strict_types=1);

namespace Shared\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'app:migrations:run-domain',
    description: 'Run migrations for a specific domain'
)]
final class RunDomainMigrationsCommand extends Command
{
    public function __construct(
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name (e.g., site)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domainArg = $input->getArgument('domain');
        if (!is_string($domainArg)) {
            throw new \InvalidArgumentException('Domain argument must be a string');
        }
        $domain = strtolower($domainArg);
        $domainPath = sprintf('%s/src/%s', $this->projectDir, ucfirst($domain));

        if (!is_dir($domainPath)) {
            $output->writeln(sprintf('<error>Domain directory "%s" not found.</error>', $domainPath));

            return Command::FAILURE;
        }

        $finder = new Finder();
        $finder
            ->directories()
            ->in($domainPath)
            ->name('Migrations')
            ->path('Resources');

        if (!$finder->hasResults()) {
            $output->writeln(sprintf('<info>No migration directories found in domain "%s".</info>', $domain));

            return Command::SUCCESS;
        }

        $application = $this->getApplication();
        if (!$application) {
            $output->writeln('<error>Could not get application instance.</error>');

            return Command::FAILURE;
        }

        // Collect all migration directories first
        $migrationDirs = iterator_to_array($finder);

        // Execute migrations one by one using separate processes
        foreach ($migrationDirs as $migrationDir) {
            $output->writeln(sprintf('<info>Running migrations in: %s</info>', $migrationDir->getPathname()));

            $configPath = dirname($migrationDir->getPathname()).'/Config/migrations.yaml';

            // Use the console command directly in a new process
            $command = sprintf(
                'php %s/bin/console doctrine:migrations:migrate --configuration=%s --no-interaction',
                $this->projectDir,
                escapeshellarg($configPath)
            );

            $process = proc_open($command, [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ], $pipes);

            if (is_resource($process)) {
                // Close stdin as we don't need it
                fclose($pipes[0]);

                // Read output and error streams
                while (!feof($pipes[1])) {
                    $data = fread($pipes[1], 4096);
                    if (is_string($data)) {
                        $output->write($data);
                    }
                }
                while (!feof($pipes[2])) {
                    $data = fread($pipes[2], 4096);
                    if (is_string($data)) {
                        $output->write($data);
                    }
                }

                // Close all pipes
                fclose($pipes[1]);
                fclose($pipes[2]);

                // Get the exit status
                $exitCode = proc_close($process);

                if (0 !== $exitCode) {
                    $output->writeln(sprintf('<error>Migration failed with exit code %d</error>', $exitCode));

                    return Command::FAILURE;
                }
            } else {
                $output->writeln('<error>Failed to start migration process</error>');

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
