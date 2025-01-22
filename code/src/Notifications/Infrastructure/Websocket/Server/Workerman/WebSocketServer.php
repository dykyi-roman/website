<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Websocket\Server\Workerman;

use Notifications\DomainModel\Server\WebSocketServerInterface;
use Psr\Log\LoggerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Websocket;
use Workerman\Worker;

final class WebSocketServer implements WebSocketServerInterface
{
    private static array $connections = [];
    private static array $authorizedConnections = [];
    private Worker $worker;
    private Worker $tcpWorker;

    public function __construct(
        private readonly LoggerInterface $logger,
        readonly string $websocketHost,
        readonly int $websocketPort,
        readonly int $websocketInternalPort,
    ) {
        // Create WebSocket worker
        $this->worker = new Worker(sprintf('websocket://%s:%d', $websocketHost, $websocketPort));
        $this->worker->count = 1;
        $this->worker->name = 'WebSocketServer';

        // Set up protocol handlers
        $this->worker->onWorkerStart = function () use ($websocketHost, $websocketInternalPort) {
            // Create TCP server in the same process after WebSocket server starts
            $connectionString = sprintf('tcp://%s:%d', $websocketHost, $websocketInternalPort);
            $this->tcpWorker = new Worker($connectionString);
            $this->tcpWorker->reusePort = false;

            $this->tcpWorker->onMessage = [$this, 'handleMessage'];
            $this->tcpWorker->listen();

            $this->logger->info('Workers started successfully', [
                'websocket' => $connectionString,
                'tcp' => $connectionString,
                'pid' => getmypid(),
            ]);
        };

        $this->worker->onConnect = function (TcpConnection $connection) {
            $connection->protocol = Websocket::class;
            self::$connections[$connection->id] = $connection;

            $this->logger->info('New WebSocket connection', [
                'connection_id' => $connection->id,
                'total_connections' => count(self::$connections),
                'pid' => getmypid(),
            ]);
        };

        $this->setupWebSocketHandlers();
    }

    public function handleMessage($connection, $data): void
    {
        $this->logger->info('Received TCP message', [
            'data' => $data,
            'pid' => getmypid(),
            'memory_usage' => memory_get_usage(true),
        ]);

        try {
            $message = json_decode($data, true);
            if (!$message || !isset($message['user_id']) || !isset($message['message'])) {
                throw new \InvalidArgumentException('Invalid message format');
            }

            $userId = $message['user_id'];

            $this->logger->info('Processing TCP message', [
                'user_id' => $userId,
                'authorized_connections' => array_keys(self::$authorizedConnections),
                'has_connection' => isset(self::$authorizedConnections[$userId]),
                'pid' => getmypid(),
            ]);

            if (!isset(self::$authorizedConnections[$userId])) {
                $this->logger->warning('User connection not found', [
                    'user_id' => $userId,
                    'authorized_users' => array_keys(self::$authorizedConnections),
                    'total_authorized' => count(self::$authorizedConnections),
                    'pid' => getmypid(),
                ]);

                return;
            }

            $userConnection = self::$authorizedConnections[$userId];
            if (TcpConnection::STATUS_CLOSED === $userConnection->getStatus()) {
                $this->logger->warning('User connection is closed', [
                    'user_id' => $userId,
                    'connection_id' => $userConnection->id,
                    'status' => $userConnection->getStatus(),
                ]);
                unset(self::$authorizedConnections[$userId]);

                return;
            }

            $userConnection->send(
                json_encode([
                    'type' => 'notification',
                    'message' => $message['message'],
                ])
            );
            $this->logger->info('Message sent to user', [
                'user_id' => $userId,
                'connection_id' => $userConnection->id,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Error processing TCP message', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    private function setupWebSocketHandlers(): void
    {
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            try {
                $this->logger->info('WebSocket message received', [
                    'data' => $data,
                    'connection_id' => $connection->id,
                    'pid' => getmypid(),
                ]);

                $message = json_decode($data, true);
                if (!$message) {
                    throw new \InvalidArgumentException('Invalid JSON message');
                }

                switch ($message['type'] ?? null) {
                    case 'authenticate':
                        if (!isset($message['userId'])) {
                            throw new \InvalidArgumentException('Missing userId in authentication message');
                        }

                        $userId = $message['userId'];

                        if (isset(self::$authorizedConnections[$userId])) {
                            $oldConnection = self::$authorizedConnections[$userId];
                            $this->logger->info('Closing old connection', [
                                'user_id' => $userId,
                                'old_connection_id' => $oldConnection->id,
                                'new_connection_id' => $connection->id,
                            ]);
                            $oldConnection->close();
                        }

                        self::$authorizedConnections[$userId] = $connection;

                        $this->logger->info('User authenticated', [
                            'user_id' => $userId,
                            'connection_id' => $connection->id,
                            'total_authorized' => count(self::$authorizedConnections),
                            'authorized_users' => array_keys(self::$authorizedConnections),
                            'pid' => getmypid(),
                        ]);

                        $connection->send(json_encode([
                            'type' => 'auth_success',
                            'message' => 'Authentication successful',
                            'userId' => $userId,
                            'timestamp' => date('c'),
                        ]));
                        break;

                    default:
                        $this->logger->warning('Unknown message type', [
                            'type' => $message['type'] ?? 'unknown',
                            'connection_id' => $connection->id,
                        ]);
                }
            } catch (\Throwable $exception) {
                $this->logger->error('Error processing WebSocket message', [
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                    'connection_id' => $connection->id,
                ]);

                $connection->send(json_encode([
                    'type' => 'error',
                    'message' => $exception->getMessage(),
                ]));
            }
        };

        $this->worker->onClose = function (TcpConnection $connection) {
            $this->logger->info('Connection closing', [
                'connection_id' => $connection->id,
                'pid' => getmypid(),
            ]);

            unset(self::$connections[$connection->id]);

            foreach (self::$authorizedConnections as $userId => $conn) {
                if ($conn === $connection) {
                    unset(self::$authorizedConnections[$userId]);
                    $this->logger->info('User connection removed', [
                        'user_id' => $userId,
                        'connection_id' => $connection->id,
                        'remaining_authorized' => count(self::$authorizedConnections),
                    ]);
                    break;
                }
            }
        };
    }

    public function run(): void
    {
        Worker::runAll();
    }
}
