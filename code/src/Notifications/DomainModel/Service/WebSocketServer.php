<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Psr\Log\LoggerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Websocket;
use Workerman\Worker;

final class WebSocketServer
{
    /**
     * Хранение всех активных соединений
     * @var array<int, \Workerman\Connection\TcpConnection>
     */
    public static array $connections = [];

    /**
     * Хранение авторизованных пользователей
     * @var array<string, \Workerman\Connection\TcpConnection>
     */
    public static array $authorizedConnections = [];

    private Worker $worker;

    public function __construct(
        private readonly LoggerInterface $logger,
        readonly string $websocketHost,
        readonly int $websocketPort,
    ) {
        // Create WebSocket worker with explicit protocol
        $this->worker = new Worker(sprintf('websocket://%s:%d', $websocketHost, $websocketPort));
        $this->worker->count = 4;
        
        // Set process name to help with identification and management
        $this->worker->name = 'WebSocketServer';

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

        $this->setupEventHandlers();
    }

    /**
     * Настройка обработчиков событий WebSocket
     */
    private function setupEventHandlers(): void
    {
        $this->worker->onMessage = function (TcpConnection $connection, $data) {
            try {
                $this->logger->info("Raw message received", [
                    'data' => $data,
                    'connection_id' => $connection->id,
                    'active_connections' => count(self::$connections)
                ]);

                $message = json_decode($data, true);
                
                $this->logger->info("Decoded message", [
                    'connection_id' => $connection->id,
                    'message_type' => $message['type'] ?? 'unknown',
                    'message_content' => $message
                ]);

                // Handle different message types
                switch ($message['type'] ?? null) {
                    case 'connect':
                        // Send acknowledgment
                        $response = [
                            'type' => 'connection_ack',
                            'message' => 'Connection established successfully',
                            'timestamp' => date('c'),
                            'debug' => [
                                'connection_id' => $connection->id,
                                'active_connections' => count(self::$connections),
                                'authorized_users' => count(self::$authorizedConnections)
                            ]
                        ];
                        $connection->send(json_encode($response));
                        $this->logger->info("Sent connection acknowledgment", [
                            'response' => $response,
                            'active_connections' => count(self::$connections)
                        ]);
                        break;

                    case 'status':
                        // Return connection status
                        $response = [
                            'type' => 'status',
                            'timestamp' => date('c'),
                            'status' => [
                                'active_connections' => count(self::$connections),
                                'authorized_users' => count(self::$authorizedConnections),
                                'connection_id' => $connection->id,
                                'is_authorized' => in_array($connection, self::$authorizedConnections, true)
                            ]
                        ];
                        $connection->send(json_encode($response));
                        break;
                    
                    case 'authenticate':
                        if (isset($message['userId'])) {
                            $this->authorizeConnection($connection, $message['userId']);
                            $response = [
                                'type' => 'auth_success',
                                'userId' => $message['userId'],
                                'timestamp' => date('c'),
                                'debug' => [
                                    'connection_id' => $connection->id,
                                    'active_connections' => count(self::$connections),
                                    'authorized_users' => count(self::$authorizedConnections)
                                ]
                            ];
                            $connection->send(json_encode($response));
                            $this->logger->info("User authenticated", [
                                'userId' => $message['userId'],
                                'active_connections' => count(self::$connections),
                                'authorized_users' => count(self::$authorizedConnections)
                            ]);
                        }
                        break;
                    
                    case 'ping':
                        $connection->send(json_encode([
                            'type' => 'pong',
                            'timestamp' => date('c'),
                            'debug' => [
                                'connection_id' => $connection->id,
                                'active_connections' => count(self::$connections)
                            ]
                        ]));
                        break;

                    default:
                        $this->logger->warning("Unhandled message type", [
                            'type' => $message['type'] ?? 'null',
                            'connection_id' => $connection->id,
                            'message' => $message,
                            'active_connections' => count(self::$connections)
                        ]);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Error processing message", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'raw_data' => $data,
                    'active_connections' => count(self::$connections)
                ]);

                // Send error response to client
                try {
                    $connection->send(json_encode([
                        'type' => 'error',
                        'message' => 'Failed to process message',
                        'timestamp' => date('c')
                    ]));
                } catch (\Throwable $e) {
                    $this->logger->error("Failed to send error response", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        };

        $this->worker->onClose = function (TcpConnection $connection) {
            // Remove from active connections
            unset(self::$connections[$connection->id]);
            
            // Remove from authorized users if present
            foreach (self::$authorizedConnections as $userId => $conn) {
                if ($conn === $connection) {
                    unset(self::$authorizedConnections[$userId]);
                    break;
                }
            }
            
            $this->logger->info("Connection closed", [
                'connection_id' => $connection->id,
                'remaining_connections' => count(self::$connections),
                'authorized_users' => count(self::$authorizedConnections)
            ]);
        };
    }

    /**
     * Авторизация соединения для конкретного пользователя
     */
    private function authorizeConnection(TcpConnection $connection, string $userId): void
    {
        self::$authorizedConnections[$userId] = $connection;
        
        $this->logger->info("Connection authorized", [
            'connection_id' => $connection->id,
            'user_id' => $userId,
            'total_authorized' => count(self::$authorizedConnections)
        ]);
    }

    public function removeAuthorizedConnection(string $userId): void
    {
        if (isset(self::$authorizedConnections[$userId])) {
            unset(self::$authorizedConnections[$userId]);
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