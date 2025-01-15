<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\ErrorHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\ErrorHandler\GlobalErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(GlobalErrorHandler::class)]
final class GlobalErrorHandlerTest extends TestCase
{
    private GlobalErrorHandler $errorHandler;
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        $this->errorHandler = new GlobalErrorHandler();
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testOnKernelExceptionWithJsonContentType(): void
    {
        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        $exception = new \Exception('Test error message');

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->errorHandler->onKernelException($event);

        $response = $event->getResponse();

        $this->assertNotNull($response);
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $decodedContent = json_decode($content, true);
        $this->assertNotNull($decodedContent);
        $this->assertFalse($decodedContent['success']);
        $this->assertEquals('Test error message', $decodedContent['errors']['message']);
    }

    public function testOnKernelExceptionWithNonJsonContentType(): void
    {
        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $exception = new \Exception('Test error message');

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->errorHandler->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testGetSubscribedEvents(): void
    {
        $events = GlobalErrorHandler::getSubscribedEvents();

        $this->assertNotNull($events);
        $this->assertArrayHasKey(KernelEvents::EXCEPTION, $events);
        $this->assertEquals('onKernelException', $events[KernelEvents::EXCEPTION]);
    }
}
