<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\HttpClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\Infrastructure\HttpClient\HttpClientLoggingMiddleware;
use Shared\Infrastructure\HttpClient\HttpClientLoggingMiddlewareFactory;

#[CoversClass(HttpClientLoggingMiddlewareFactory::class)]
final class HttpClientLoggingMiddlewareFactoryTest extends TestCase
{
    public function testCreateReturnsCallable(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $middleware = new HttpClientLoggingMiddleware($logger);
        $factory = new HttpClientLoggingMiddlewareFactory($middleware);

        $handler = $factory->create();

        self::assertIsCallable($handler);
    }
}
