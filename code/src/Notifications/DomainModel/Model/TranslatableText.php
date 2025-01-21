<?php

declare(strict_types=1);

namespace Notifications\DomainModel\Model;

final readonly class TranslatableText
{
    public function __construct(
        private string $messageId,
        /** @var array<string, string|int|float|bool|null> */
        private array $parameters = [],
    ) {
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return array<string, string|int|float|bool|null>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
