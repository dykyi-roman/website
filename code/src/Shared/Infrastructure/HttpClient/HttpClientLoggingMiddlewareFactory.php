<?php

declare(strict_types=1);

namespace Shared\Infrastructure\HttpClient;

class HttpClientLoggingMiddlewareFactory
{
    public function __construct(
        private HttpClientLoggingMiddleware $middleware
    ) {
    }

    public function create(): callable
    {
        return ($this->middleware)();
    }
}
