<?php

declare(strict_types=1);

namespace Notifications\Presentation\Web;

use Notifications\DomainModel\Service\RealTimeNotificationDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final readonly class SSENotificationAction
{
    public function __construct(
        private RealTimeNotificationDispatcher $notificationDispatcher
    ) {
    }

    #[Route('/sse', name: 'sse_notifications', methods: ['GET'])]
    public function stream(): Response
    {
        // Игнорируем разрыв соединения клиентом
        ignore_user_abort(true);

        $response = new StreamedResponse(function () {
            // Отключаем буферизацию
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Важные настройки PHP для SSE
            set_time_limit(0);
            ini_set('output_buffering', '0');
            ini_set('implicit_flush', '1');

            // Отправляем начальное сообщение для установки соединения
            echo "retry: 3000\n";    // Установка интервала переподключения
            echo ": ping\n\n";       // Начальный ping
            flush();

            while (true) {
                $notifications = $this->notificationDispatcher->getNotifications();

                if (!empty($notifications)) {
                    foreach ($notifications as $notification) {
                        echo "id: " . uniqid('notif_', true) . "\n";
                        echo "event: notification\n";
                        echo "data: " . json_encode($notification) . "\n\n";
                        flush();
                    }
                } else {
                    // Отправляем ping для поддержания соединения
                    echo ": ping " . time() . "\n\n";
                    flush();
                }

                // Проверяем состояние соединения
                if (connection_status() !== CONNECTION_NORMAL) {
                    break;
                }

                // Спим чтобы не нагружать CPU
                usleep(500000); // 500ms
            }
        });

        // Устанавливаем заголовки
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('X-Accel-Buffering', 'no');

        // CORS заголовки
        $response->headers->set('Access-Control-Allow-Origin', 'https://127.0.0.1:1001');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }

    #[Route('/sse', name: 'sse_notifications_preflight', methods: ['OPTIONS'])]
    public function preflight(): Response
    {
        $response = new Response();

        // CORS заголовки для preflight запроса
        $response->headers->set('Access-Control-Allow-Origin', 'https://127.0.0.1:1001');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 часа

        return $response;
    }
}
