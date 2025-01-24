<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Websocket\Client;

use Notifications\DomainModel\Exception\SendSocketMessageException;
use Notifications\DomainModel\Service\NotificationDispatcherInterface;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\ValueObject\UserId;

final readonly class UnixSocketClient implements NotificationDispatcherInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $websocketHost,
        private int $websocketInternalPort,
    ) {
    }

    /**
     * @param array<string, mixed> $message
     *
     * @throws SendSocketMessageException
     */
    public function dispatch(UserId $userId, array $message): void
    {
        try {
            $this->logger->info('Starting notification dispatch', [
                'user_id' => $userId->toRfc4122(),
                'host' => $this->websocketHost,
                'port' => $this->websocketInternalPort,
            ]);

            $connectionString = sprintf('tcp://%s:%d', $this->websocketHost, $this->websocketInternalPort);
            $client = stream_socket_client($connectionString, $errno, $errstr);
            if (!$client) {
                throw new \RuntimeException("Could not connect to socket: $errstr (error: $errno)");
            }

            $this->logger->info('Connected to socket server', [
                'socket' => $connectionString,
                'resource' => get_resource_type($client),
            ]);

            $data = json_encode([
                'user_id' => $userId->toRfc4122(),
                'message' => $message,
            ]);

            if (false === $data) {
                throw new \RuntimeException('Failed to encode message data as JSON');
            }

            $this->logger->info('Sending data to socket', [
                'data' => $data,
                'data_length' => strlen($data),
            ]);

            $bytesWritten = fwrite($client, $data."\n");
            if (false === $bytesWritten) {
                throw new \RuntimeException('Failed to write to socket');
            }

            $this->logger->info('Data written to socket', [
                'bytes_written' => $bytesWritten,
            ]);

            fclose($client);

            $this->logger->info('Notification dispatch completed', [
                'user_id' => $userId->toRfc4122(),
                'bytes_sent' => $bytesWritten,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Error dispatching notification', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'user_id' => $userId->toRfc4122(),
            ]);

            throw new SendSocketMessageException($exception->getMessage(), 0, $exception);
        }
    }
}
