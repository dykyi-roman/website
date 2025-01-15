<?php

declare(strict_types=1);

namespace Shared\Tests\Unit\Infrastructure\MessageBus;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shared\Infrastructure\MessageBus\SymfonyMessageBus;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

#[CoversClass(SymfonyMessageBus::class)]
final class SymfonyMessageBusTest extends TestCase
{
    private MessageBusInterface $symfonyBus;
    private SymfonyMessageBus $messageBus;

    protected function setUp(): void
    {
        $this->symfonyBus = $this->getMockBuilder(MessageBusInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageBus = new SymfonyMessageBus($this->symfonyBus);
    }

    public function testDispatchWithResult(): void
    {
        $message = new \stdClass();
        $expectedResult = 'test result';

        $envelope = new Envelope($message, [
            new HandledStamp($expectedResult, 'test.handler'),
        ]);

        $this->symfonyBus->expects(self::once())
            ->method('dispatch')
            ->with($message)
            ->willReturn($envelope);

        $result = $this->messageBus->dispatch($message);
        self::assertSame($expectedResult, $result);
    }

    public function testDispatchWithoutHandledStamp(): void
    {
        $message = new \stdClass();
        $envelope = new Envelope($message);

        $this->symfonyBus->expects(self::once())
            ->method('dispatch')
            ->with($message)
            ->willReturn($envelope);

        $result = $this->messageBus->dispatch($message);
        self::assertNull($result);
    }

    public function testDispatchWithException(): void
    {
        $message = new \stdClass();
        $originalException = new \RuntimeException('Original error');
        $busException = new \Exception('Bus error', 0, $originalException);

        $this->symfonyBus->expects(self::once())
            ->method('dispatch')
            ->with($message)
            ->willThrowException($busException);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Original error');

        $this->messageBus->dispatch($message);
    }

    public function testDispatchWithDirectException(): void
    {
        $message = new \stdClass();
        $exception = new \RuntimeException('Direct error');

        $this->symfonyBus->expects(self::once())
            ->method('dispatch')
            ->with($message)
            ->willThrowException($exception);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Direct error');

        $this->messageBus->dispatch($message);
    }
}
