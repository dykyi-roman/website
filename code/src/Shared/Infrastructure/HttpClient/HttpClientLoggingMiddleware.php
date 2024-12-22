<?php

declare(strict_types=1);

namespace Shared\Infrastructure\HttpClient;

use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final readonly class HttpClientLoggingMiddleware
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(): callable
    {
        return Middleware::tap(
            function (RequestInterface $request) {
                $this->logRequest($request);
            },
            function (RequestInterface $request, array $options, PromiseInterface $response) {
                $response->then(
                    function (ResponseInterface $response) use ($request) {
                        $this->logResponse($request, $response);
                    }
                );
            }
        );
    }

    private function logRequest(RequestInterface $request): void
    {
        $this->logger->info('HTTP Request', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody(),
        ]);
    }

    private function logResponse(RequestInterface $request, ResponseInterface $response): void
    {
        $this->logger->info('HTTP Response', [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
        ]);
    }
}
