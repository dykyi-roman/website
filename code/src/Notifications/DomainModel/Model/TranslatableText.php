<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Model;

final readonly class TranslatableText
{
    public function __construct(
        private string $messageId,
        private array $parameters = []
    ) {
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
