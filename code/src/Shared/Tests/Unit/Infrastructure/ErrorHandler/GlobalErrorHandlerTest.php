<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\ErrorHandler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shared\Infrastructure\ErrorHandler\GlobalErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[CoversClass(GlobalErrorHandler::class)]
final class GlobalErrorHandlerTest extends TestCase
{
    private GlobalErrorHandler $errorHandler;
    private HttpKernelInterface $kernel;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->errorHandler = new GlobalErrorHandler($this->logger);
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public static function exceptionTypeProvider(): array
    {
        return [
            'http_exception' => [
                new HttpException(Response::HTTP_NOT_FOUND, 'Not Found'),
                Response::HTTP_NOT_FOUND,
                'http_error',
                'http_exception',
            ],
            'validation_exception' => [
                new ValidationFailedException('entity', new ConstraintViolationList()),
                Response::HTTP_BAD_REQUEST,
                'validation_error',
                'validation_failed_exception',
            ],
            'domain_exception' => [
                new \DomainException('Domain error'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'domain_error',
                'domain_exception',
            ],
            'invalid_argument_exception' => [
                new \InvalidArgumentException('Invalid argument'),
                Response::HTTP_BAD_REQUEST,
                'logic_error',
                'invalid_argument_exception',
            ],
            'logic_exception' => [
                new \LogicException('Logic error'),
                Response::HTTP_CONFLICT,
                'logic_error',
                'logic_exception',
            ],
            'runtime_exception' => [
                new \RuntimeException('Runtime error'),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'runtime_error',
                'runtime_exception',
            ],
        ];
    }

    #[DataProvider('exceptionTypeProvider')]
    public function testHandlesDifferentExceptionTypes(
        \Throwable $exception,
        int $expectedStatusCode,
        string $expectedErrorType,
        string $expectedErrorCode,
    ): void {
        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        if ($expectedStatusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->expects($this->once())
                ->method('error')
                ->with(
                    $exception->getMessage(),
                    $this->callback(function ($context) use ($exception) {
                        return isset($context['exception'])
                            && isset($context['trace'])
                            && $context['exception'] === $exception;
                    })
                );
        } else {
            $this->logger->expects($this->never())->method('error');
        }

        $this->errorHandler->onKernelException($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $decodedContent = json_decode($content, true);

        $this->assertNotNull($decodedContent);
        $this->assertFalse($decodedContent['success']);
        $this->assertEquals($expectedErrorType, $decodedContent['error']['type']);
        $this->assertEquals($expectedErrorCode, $decodedContent['error']['code']);
        $this->assertEquals($exception->getMessage(), $decodedContent['error']['message']);
    }

    public function testHandlesValidationException(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'Invalid email',
                null,
                [],
                'root',
                'email',
                'test',
                null,
                'EMAIL_ERROR'
            ),
            new ConstraintViolation(
                'Password too short',
                null,
                [],
                'root',
                'password',
                'test',
                null,
                'PASSWORD_LENGTH'
            ),
        ]);

        $exception = new ValidationFailedException('entity', $violations);

        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        $event = new ExceptionEvent(
            $this->kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->errorHandler->onKernelException($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $decodedContent = json_decode($content, true);

        $this->assertNotNull($decodedContent);
        $this->assertFalse($decodedContent['success']);
        $this->assertEquals('validation_error', $decodedContent['error']['type']);
        $this->assertCount(2, $decodedContent['error']['violations']);

        $this->assertEquals('email', $decodedContent['error']['violations'][0]['property']);
        $this->assertEquals('Invalid email', $decodedContent['error']['violations'][0]['message']);
        $this->assertEquals('EMAIL_ERROR', $decodedContent['error']['violations'][0]['code']);

        $this->assertEquals('password', $decodedContent['error']['violations'][1]['property']);
        $this->assertEquals('Password too short', $decodedContent['error']['violations'][1]['message']);
        $this->assertEquals('PASSWORD_LENGTH', $decodedContent['error']['violations'][1]['code']);
    }

    public function testIgnoresNonJsonRequests(): void
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
        $this->assertEquals(['onKernelException', 0], $events[KernelEvents::EXCEPTION]);
    }
}
