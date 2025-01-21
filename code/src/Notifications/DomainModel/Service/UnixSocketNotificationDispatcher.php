<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Service;

use Notifications\DomainModel\Model\UserNotification;
use Psr\Log\LoggerInterface;

final readonly class UnixSocketNotificationDispatcher
{
    private const string HOST = '127.0.0.1';
    private const int PORT = 2206;

    public function __construct(
        private NotificationFormatter $notificationFormatter,
        private LoggerInterface $logger,
    ) {
    }

    public function dispatch(UserNotification $userNotification): void
    {
        try {
            $message = $this->notificationFormatter->transform($userNotification);
            
            $client = stream_socket_client('tcp://' . self::HOST . ':' . self::PORT, $errno, $errstr);
            if (!$client) {
                throw new \RuntimeException("Could not connect to socket: $errstr");
            }
            
            $data = json_encode([
                'user_id' => $userNotification->getUserId()->toRfc4122(),
                'message' => $message
            ]);
            
            fwrite($client, $data . "\n");
            fclose($client);
            
            $this->logger->info('Notification dispatched through TCP Socket', [
                'user_id' => $userNotification->getUserId(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Error dispatching notification', [
                'error' => $e->getMessage(),
                'user_id' => $userNotification->getUserId()
            ]);
            throw $e;
        }
    }
}
