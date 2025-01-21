<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Psr\Log\LoggerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Websocket;
use Workerman\Worker;

final class WebSocketServer
{
    private const string SOCKET_FILE = '/tmp/workerman.sock';
    private const string REDIS_USER_PREFIX = 'ws:user:';

    /**
     * Хранение всех активных соединений
     * @var array<int, \Workerman\Connection\TcpConnection>
     */
    private static array $connections = [];

    /**
     * Хранение авторизованных пользователей
     * @var array<string, \Workerman\Connection\TcpConnection>
     */
    private static array $authorizedConnections = [];

    private Worker $worker;
    private Worker $innerWorker;

    public function __construct(
        private readonly LoggerInterface $logger,
        readonly string $websocketHost,
        readonly int $websocketPort,
    ) {
        // Create WebSocket worker
        $this->worker = new Worker(sprintf('websocket://%s:%d', $websocketHost, $websocketPort));
        $this->worker->count = 4;
        $this->worker->name = 'WebSocketServer';

        // Create inner communication worker using TCP
        $this->innerWorker = new Worker('text://127.0.0.1:2206');
        $this->innerWorker->count = 1;
        $this->innerWorker->name = 'InnerCommunication';
        
        // Set up protocol handlers
        $this->worker->onWorkerStart = function () use ($websocketHost, $websocketPort) {
            $this->logger->info('WebSocket worker started successfully', [
                'host' => $websocketHost,
                'port' => $websocketPort
            ]);
        };

        $this->worker->onConnect = function ($connection) {
            // Set protocol handler
            $connection->protocol = Websocket::class;
            
            // Store the connection
            self::$connections[$connection->id] = $connection;
            
            $this->logger->info("New connection established", [
                'connection_id' => $connection->id,
                'ip' => $connection->getRemoteIp(),
                'total_connections' => count(self::$connections)
            ]);
        };

        // Set up inner communication handler
        $this->innerWorker->onMessage = function($connection, $data) {
            $this->handleInnerMessage(json_decode($data, true));
        };

        $this->setupEventHandlers();
    }

    private function handleInnerMessage(array $data): void
    {
        if (!isset($data['user_id']) || !isset($data['message'])) {
            $this->logger->error('Invalid message format received', ['data' => $data]);
            return;
        }

        $userId = $data['user_id'];
        if (!isset(self::$authorizedConnections[$userId])) {
            $this->logger->warning('User connection not found', ['user_id' => $userId]);
            return;
        }

        try {
            self::$authorizedConnections[$userId]->send($data['message']);
            $this->logger->info('Message sent to user', ['user_id' => $userId]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send message', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function setupEventHandlers(): void
    {
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            try {
                $this->logger->info("Raw message received", [
                    'data' => $data,
                    'connection_id' => $connection->id
                ]);

                $message = json_decode($data, true);
                if (!$message) {
                    throw new \InvalidArgumentException('Invalid JSON message');
                }
                
                $this->logger->info("Decoded message", [
                    'connection_id' => $connection->id,
                    'message_type' => $message['type'] ?? 'unknown',
                ]);

                // Handle different message types
                switch ($message['type'] ?? null) {
                    case 'authenticate':
                        if (!isset($message['userId'])) {
                            throw new \InvalidArgumentException('Missing userId in authentication message');
                        }
                        
                        $this->authorizeConnection($connection, $message['userId']);
                        
                        // Send acknowledgment
                        $response = [
                            'type' => 'auth_success',
                            'message' => 'Authentication successful',
                            'userId' => $message['userId'],
                            'timestamp' => date('c')
                        ];
                        $connection->send(json_encode($response));
                        break;
                        
                    default:
                        $this->logger->warning("Unknown message type", [
                            'type' => $message['type'] ?? 'unknown',
                            'connection_id' => $connection->id
                        ]);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Error processing message", [
                    'error' => $e->getMessage(),
                    'connection_id' => $connection->id
                ]);
                
                $connection->send(json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage()
                ]));
            }
        };

        $this->worker->onClose = function (TcpConnection $connection) {
            // Remove from connections pool
            unset(self::$connections[$connection->id]);
            
            // Remove from authorized connections
            foreach (self::$authorizedConnections as $userId => $conn) {
                if ($conn === $connection) {
                    unset(self::$authorizedConnections[$userId]);
                    $this->logger->info("User connection closed", ['userId' => $userId]);
                    break;
                }
            }
            
            $this->logger->info("Connection closed", [
                'connection_id' => $connection->id,
                'remaining_connections' => count(self::$connections)
            ]);
        };
    }

    /**
     * Авторизация соединения для конкретного пользователя
     */
    private function authorizeConnection(TcpConnection $connection, string $userId): void
    {
        self::$authorizedConnections[$userId] = $connection;
        $this->logger->info("User authorized", [
            'userId' => $userId,
            'connection_id' => $connection->id
        ]);
    }

    /**
     * Store user connection in Redis
     */
    private function storeUserConnection(string $userId, int $connectionId): void
    {
        try {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->set(self::REDIS_USER_PREFIX . $userId, $connectionId);
            $redis->expire(self::REDIS_USER_PREFIX . $userId, 3600); // 1 hour TTL
        } catch (\Throwable $e) {
            $this->logger->error('Error storing user connection', [
                'user_id' => $userId,
                'connection_id' => $connectionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove user connection from Redis
     */
    private function removeUserConnection(string $userId): void
    {
        try {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $redis->delete(self::REDIS_USER_PREFIX . $userId);
        } catch (\Throwable $e) {
            $this->logger->error('Error removing user connection', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function removeAuthorizedConnection(string $userId): void
    {
        if (isset(self::$authorizedConnections[$userId])) {
            unset(self::$authorizedConnections[$userId]);
            $this->removeUserConnection($userId);
            $this->logger->info("User connection removed", ['userId' => $userId]);
        }
    }

    public static function pushToUser(string $userId, array $data): bool
    {
        if (!isset(self::$authorizedConnections[$userId])) {
            return false;
        }

        self::$authorizedConnections[$userId]->send(json_encode($data));
        return true;
    }

    public static function pushToAll(array $data): void
    {
        $message = json_encode($data);
        foreach (self::$connections as $connection) {
            $connection->send($message);
        }
    }

    public static function pushToAuthorized(array $data): void
    {
        $message = json_encode($data);
        foreach (self::$authorizedConnections as $connection) {
            $connection->send($message);
        }
    }

    private function sendError(TcpConnection $connection, string $code, string $message): void
    {
        $connection->send(
            json_encode([
                'type' => 'error',
                'code' => $code,
                'message' => $message
            ])
        );
    }

    public function run(): void
    {
        Worker::runAll();
    }

    public static function getConnectionsCount(): int
    {
        return count(self::$connections);
    }

    public static function getAuthorizedCount(): int
    {
        return count(self::$authorizedConnections);
    }
}