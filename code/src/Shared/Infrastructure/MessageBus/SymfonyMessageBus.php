<?php

declare(strict_types=1);

namespace Shared\Infrastructure\MessageBus;

use Shared\DomainModel\Services\MessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface as SymfonyMessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class SymfonyMessageBus implements MessageBusInterface
{
    public function __construct(
        private SymfonyMessageBusInterface $messageBus,
    ) {
    }

    public function dispatch(object $message): object
    {
        $envelope = $this->messageBus->dispatch($message);

        /** @var HandledStamp|null $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @phpstan-ignore-next-line */
        return $stamp?->getResult();
    }
}
