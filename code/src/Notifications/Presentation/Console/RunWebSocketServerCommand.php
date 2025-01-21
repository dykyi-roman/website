<?php

declare(strict_types=1);

namespace Notifications\Presentation\Console;

use Notifications\DomainModel\Service\WebSocketServer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:websocket:run',
    description: 'Run WebSocket server'
)]
final class RunWebSocketServerCommand extends Command
{
    public function __construct(
        private readonly WebSocketServer $webSocketServer,
        private readonly LoggerInterface $logger,
        private readonly string $websocketHost,
        private readonly int $websocketPort,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::OPTIONAL, 'Action to perform: start', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $action = $input->getArgument('action');
            $output->writeln(sprintf('Performing WebSocket server action: %s', $action));

            // Perform the requested action
            switch ($action) {
                case 'start':
                    // Stop any existing processes on the port before starting
                    exec(sprintf('sudo lsof -t -i:%d | xargs kill -9 2>/dev/null', $this->websocketPort));

                    $this->logger->info('Starting WebSocket server', [
                        'host' => $this->websocketHost,
                        'port' => $this->websocketPort
                    ]);
                    $this->webSocketServer->run();
                    break;
                default:
                    $output->writeln("Invalid action. Currently only 'start' is supported.");
                    return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('Error: ' . $e->getMessage());
            $this->logger->error('WebSocket server startup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}
