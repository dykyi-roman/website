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
    private static array $connections = [];

    /**
     * Хранение авторизованных пользователей
     * @var array<string, \Workerman\Connection\TcpConnection>
     */
    private static array $authorizedConnections = [];

    private Worker $worker;

    public function __construct(
        private readonly LoggerInterface $logger,
        readonly string $websocketHost,
        readonly int $websocketPort,
    ) {
        // Explicitly set host and port
        $this->worker = new Worker(sprintf('websocket://%s:%d', $websocketHost, $websocketPort));
        $this->worker->count = 4;
        
        // Set process name to help with identification and management
        $this->worker->name = 'WebSocketServer';
        
        // Add error handling for worker
        $this->worker->onWorkerStart = function () {
            $this->logger->info('WebSocket worker started successfully');
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
            $this->logger->info("Start new connection", [
                'connection_id' => $connection->id,
                'ip' => $connection->getRemoteIp()
            ]);
        };

        $this->worker->onMessage = function (TcpConnection $connection, string $message): void {
            try {
                $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                $this->handleMessage($connection, $data);
            } catch (\JsonException $e) {
                $this->logger->error("Ошибка декодирования сообщения", [
                    'error' => $e->getMessage(),
                    'message' => $message
                ]);
                $this->sendError($connection, 'invalid_message', 'Неверный формат сообщения');
            }
        };

        $this->worker->onClose = function (TcpConnection $connection): void {
            unset(self::$connections[$connection->id]);

            $userId = array_search($connection, self::$authorizedConnections, true);
            if ($userId !== false) {
                unset(self::$authorizedConnections[$userId]);
                $this->logger->info("Auth user close connection", [
                    'user_id' => $userId,
                    'connection_id' => $connection->id
                ]);
            }

            $this->logger->info("Close connection", [
                'connection_id' => $connection->id
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