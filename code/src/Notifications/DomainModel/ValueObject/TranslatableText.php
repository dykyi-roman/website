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

    /**
     * @param array<string, string|int|float|bool|null> $parameters
     */
    public static function create(string $messageId, array $parameters = []): self
    {
        return new self($messageId, $parameters);
    }

    /**
     * @param array{messageId: string, parameters: string|array<string, string|int|float|bool|null>} $data
     */
    public static function fromArray(array $data): self
    {
        $parameters = is_string($data['parameters']) 
            ? json_decode($data['parameters'], true) 
            : $data['parameters'];
            
        return new self(
            $data['messageId'],
            $parameters ?? []
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

    /**
     * @return array{messageId: string, parameters: string|false}
     */
    public function jsonSerialize(): array
    {
        return [
            'messageId' => $this->messageId,
            'parameters' => json_encode($this->parameters),
        ];
    }
}
