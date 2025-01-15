<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\HttpClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Shared\Infrastructure\HttpClient\HttpClientLoggingMiddleware;

#[CoversClass(HttpClientLoggingMiddleware::class)]
final class HttpClientLoggingMiddlewareTest extends TestCase
{
    public function testInvokeReturnsCallable(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $middleware = new HttpClientLoggingMiddleware($logger);

        $handler = ($middleware)();
        self::assertNotNull($handler);
    }

    public function testHandlerLogsRequestAndResponse(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $middleware = new HttpClientLoggingMiddleware($logger);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $logger->expects(self::exactly(2))
            ->method('info');

        $handler = ($middleware)();
        $promise = new class($response) {
            private ResponseInterface $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function then(callable $onFulfilled): mixed
            {
                return $onFulfilled($this->response);
            }
        };

        $nextHandler = fn () => $promise;

        $result = $handler($nextHandler)($request, []);
        self::assertSame($response, $result);
    }
}
