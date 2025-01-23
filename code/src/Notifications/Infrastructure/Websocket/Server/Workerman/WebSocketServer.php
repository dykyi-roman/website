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
    private const int WORKER_COUNT = 1;

    /** @var array<int, TcpConnection> */
    private static array $connections = [];
    /** @var array<string, TcpConnection> */
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
        $this->worker->count = self::WORKER_COUNT;
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

    public function handleMessage(TcpConnection $connection, string $data): void
    {
        $this->logger->info('Received TCP message', [
            'data' => $data,
            'pid' => getmypid(),
            'memory_usage' => memory_get_usage(true),
        ]);

        try {
            $message = json_decode($data, true);
            if (!is_array($message)) {
                throw new \InvalidArgumentException('Message must be a JSON object');
            }

            if (!isset($message['user_id'])) {
                throw new \InvalidArgumentException('Missing required field: user_id');
            }

            if (!isset($message['message'])) {
                throw new \InvalidArgumentException('Missing required field: message');
            }

            if (!is_string($message['user_id'])) {
                throw new \InvalidArgumentException('Field user_id must be a string');
            }

            if (!is_array($message['message'])) {
                throw new \InvalidArgumentException('Field message must be an object containing notification data');
            }

            // Validate notification message structure
            $notification = $message['message'];
            if (!isset($notification['type'], $notification['message'])) {
                throw new \InvalidArgumentException('Notification must contain type and message fields');
            }

            /** @var array{user_id: string, message: array{type: string, title: string, message: string, icon: ?string, id: string, readAt: ?string, createdAt: string, deletedAt: ?string}} $message */
            $userId = $message['user_id'];

            $this->logger->info('Processing TCP message', [
                'user_id' => $userId,
                'notification_type' => $notification['type'],
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

                /** @var array{type?: string, userId?: string}|null */
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
