<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Notification\Symfony\Channel;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

final class CustomEmailOptions implements MessageOptionsInterface
{
    /** @var array<string, mixed> */
    private array $options;

    public function __construct(string $recipientId = '')
    {
        $this->options = ['recipientId' => $recipientId];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->options;
    }

    public function getRecipientId(): ?string
    {
        return $this->options['recipientId'] ?? null;
    }

    public function recipientId(string $recipientId): self
    {
        $this->options['recipientId'] = $recipientId;

        return $this;
    }

    public function getTransport(): string
    {
        return 'custom-email';
    }
}
