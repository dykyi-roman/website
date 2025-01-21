<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Psr\Log\LoggerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

final class WebSocketServer
{
    /**
     * Хранение всех активных соединений
     * @var array<\Workerman\Connection\TcpConnection>
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
        // Try alternative ports if the primary port is in use
        $actualPort = $this->findAvailablePort($websocketHost, $websocketPort);
        
        // Explicitly set host and port
        $this->worker = new Worker(sprintf('websocket://%s:%d', $websocketHost, $actualPort));
        $this->worker->count = 4;
        
        // Set process name to help with identification and management
        $this->worker->name = 'WebSocketServer';
        
        // Add error handling for worker
        $this->worker->onWorkerStart = function () use ($actualPort) {
            $this->logger->info('WebSocket worker started successfully', [
//                'host' => $bindAddress,
                'port' => $actualPort
            ]);
        };

        $this->worker->onWorkerStop = function () {
            $this->logger->info('WebSocket worker stopped');
        };

        $this->worker->onError = function ($connection, $code, $msg) {
            $this->logger->error('WebSocket server error', [
                'code' => $code,
                'message' => $msg
            ]);
        };
        
        $this->setupEventHandlers();
    }

    /**
     * Настройка обработчиков событий WebSocket
     */
    private function setupEventHandlers(): void
    {
        $this->worker->onConnect = function (TcpConnection $connection): void {
            self::$connections[$connection->id] = $connection;
            $this->logger->info("New connection established", [
                'connection_id' => $connection->id,
                'ip' => $connection->getRemoteIp(),
                'total_connections' => count(self::$connections)
            ]);

            // Set message handling for this connection
            $connection->onMessage = function (TcpConnection $connection, $data) {
                try {
                    $message = json_decode($data, true);
                    
                    $this->logger->info("Received message", [
                        'connection_id' => $connection->id,
                        'message_type' => $message['type'] ?? 'unknown',
                        'message_content' => $data
                    ]);

                    // Handle different message types
                    switch ($message['type'] ?? null) {
                        case 'connect':
                            // Optional: Additional logic for connection message
                            $connection->send(json_encode([
                                'type' => 'connection_ack',
                                'message' => 'Connection established successfully'
                            ]));
                            break;
                        
                        case 'authenticate':
                            // Example authentication logic
                            if (isset($message['userId'])) {
                                $this->authorizeConnection($connection, $message['userId']);
                            }
                            break;
                        
                        default:
                            $this->logger->warning("Unhandled message type", [
                                'type' => $message['type'] ?? 'null',
                                'connection_id' => $connection->id
                            ]);
                    }
                } catch (\Throwable $e) {
                    $this->logger->error("Error processing message", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'raw_data' => $data
                    ]);
                }
            };
        };

        $this->worker->onClose = function (TcpConnection $connection): void {
            // Remove from connections
            if (isset(self::$connections[$connection->id])) {
                unset(self::$connections[$connection->id]);
            }

            // Find and remove from authorized connections if applicable
            foreach (self::$authorizedConnections as $userId => $authorizedConnection) {
                if ($authorizedConnection === $connection) {
                    unset(self::$authorizedConnections[$userId]);
                    break;
                }
            }

            $this->logger->info("Connection closed", [
                'connection_id' => $connection->id,
                'total_connections' => count(self::$connections)
            ]);
        };

        $this->worker->onError = function (TcpConnection $connection, \Throwable $e): void {
            $this->logger->error("Connection error:", [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        };
    }

    /**
     * Обработка входящих сообщений
     */
    private function handleMessage(TcpConnection $connection, array $data): void
    {
        if (!isset($data['type'])) {
            $this->sendError($connection, 'missing_type', 'Тип сообщения не указан');
            return;
        }

        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($connection, $data);
                break;

//            case 'subscribe':
//                $this->handleSubscribe($connection, $data);
//                break;
//
//            case 'message':
//                $this->handleClientMessage($connection, $data);
//                break;

            default:
                $this->sendError($connection, 'unknown_type', 'Неизвестный тип сообщения');
        }
    }

    /**
     * Обработка авторизации
     */
    private function handleAuth(TcpConnection $connection, array $data): void
    {
        if (!isset($data['token'])) {
            $this->sendError($connection, 'auth_failed', 'Токен не предоставлен');
            return;
        }

        // Здесь должна быть ваша логика проверки токена
        $userId = $this->validateToken($data['token']);

        if ($userId) {
            self::$authorizedConnections[$userId] = $connection;
            $connection->send(
                json_encode([
                    'type' => 'auth',
                    'status' => 'success',
                    'user_id' => $userId
                ])
            );
        } else {
            $this->sendError($connection, 'auth_failed', 'Неверный токен');
        }
    }

    /**
     * Authorize a connection for a specific user
     */
    public function authorizeConnection(TcpConnection $connection, string $userId): void
    {
        // Remove any previous connection for this user
        if (isset(self::$authorizedConnections[$userId])) {
            $this->logger->info("Replacing existing connection for user", ['userId' => $userId]);
            unset(self::$authorizedConnections[$userId]);
        }

        // Store the authorized connection
        self::$authorizedConnections[$userId] = $connection;
        
        $this->logger->info("User connection authorized", [
            'userId' => $userId,
            'connectionId' => $connection->id
        ]);
    }

    /**
     * Remove user's authorized connection
     */
    public function removeAuthorizedConnection(string $userId): void
    {
        if (isset(self::$authorizedConnections[$userId])) {
            unset(self::$authorizedConnections[$userId]);
            $this->logger->info("User connection removed", ['userId' => $userId]);
        }
    }

    /**
     * Отправка сообщения конкретному пользователю
     */
    public static function pushToUser(string $userId, array $data): bool
    {
        if (!isset(self::$authorizedConnections[$userId])) {
            return false;
        }

        self::$authorizedConnections[$userId]->send(json_encode($data));
        return true;
    }

    /**
     * Отправка сообщения всем подключенным клиентам
     */
    public static function pushToAll(array $data): void
    {
        $message = json_encode($data);
        foreach (self::$connections as $connection) {
            $connection->send($message);
        }
    }

    /**
     * Отправка сообщения всем авторизованным пользователям
     */
    public static function pushToAuthorized(array $data): void
    {
        $message = json_encode($data);
        foreach (self::$authorizedConnections as $connection) {
            $connection->send($message);
        }
    }

    /**
     * Отправка сообщения об ошибке клиенту
     */
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

    /**
     * Проверка токена (пример)
     */
    private function validateToken(string $token): ?string
    {
        // Здесь должна быть ваша логика валидации токена
        // Возвращает ID пользователя или null
        return null;
    }

    /**
     * Find an available port, starting from the specified port
     */
    private function findAvailablePort(string $host, int $startPort, int $maxAttempts = 10): int
    {
        $currentPort = $startPort;
        
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                $bindResult = @socket_bind($socket, $host, $currentPort);
                socket_close($socket);
                
                if ($bindResult) {
                    $this->logger->info('Found available port', [
                        'host' => $host,
                        'port' => $currentPort,
                        'attempt' => $attempt + 1
                    ]);
                    return $currentPort;
                }
            } catch (\Throwable $e) {
                $this->logger->warning('Port binding attempt failed', [
                    'host' => $host,
                    'port' => $currentPort,
                    'error' => $e->getMessage()
                ]);
            }
            
            $currentPort++;
        }
        
        throw new \RuntimeException("Could not find an available port after $maxAttempts attempts");
    }

    /**
     * Запуск WebSocket сервера
     */
    public function run(): void
    {
        Worker::runAll();
    }

    /**
     * Получение количества активных соединений
     */
    public static function getConnectionsCount(): int
    {
        return count(self::$connections);
    }

    /**
     * Получение количества авторизованных пользователей
     */
    public static function getAuthorizedCount(): int
    {
        return count(self::$authorizedConnections);
    }
}