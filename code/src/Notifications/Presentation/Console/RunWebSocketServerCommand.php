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
        private readonly string $websocketHost = '127.0.0.1',
        private readonly int $websocketPort = 1001
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
        } catch (\Throwable $exception) {
            $this->logger->error('WebSocket server action failed', [
                'exception' => $exception,
                'action' => $action
            ]);

            $output->writeln(sprintf('Error: %s', $exception->getMessage()));

            return Command::FAILURE;
        }
    }
}
