<?php

declare(strict_types=1);

namespace Notifications\DomainModel\ValueObject;

final readonly class TranslatableText implements \JsonSerializable
{
    private function __construct(
        private string $messageId,
        /** @var array<string, string|int|float|bool|null> */
        private array $parameters = [],
    ) {
    }

    public static function create(string $messageId, array $parameters = []): self
    {
        return new self($messageId, $parameters);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['messageId'],
            (array) json_decode($data['parameters']),
        );
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

    public function jsonSerialize(): array
    {
        return [
            'messageId' => $this->messageId,
            'parameters' => json_encode($this->parameters),
        ];
    }
}
