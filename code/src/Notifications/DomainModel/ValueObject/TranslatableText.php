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
        if (!isset($data['messageId']) || !is_string($data['messageId'])) {
            throw new \InvalidArgumentException('Invalid or missing messageId');
        }

        if (!isset($data['parameters'])) {
            return new self($data['messageId'], []);
        }

        if (is_string($data['parameters'])) {
            $decoded = json_decode($data['parameters'], true);
            if (!is_array($decoded)) {
                throw new \InvalidArgumentException('Invalid JSON in parameters string');
            }
            $parameters = $decoded;
        } else if (!is_array($data['parameters'])) {
            throw new \InvalidArgumentException('Parameters must be string or array');
        } else {
            $parameters = $data['parameters'];
        }

        // Validate parameter values
        foreach ($parameters as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Parameter keys must be strings');
            }
            if (!is_null($value) && !is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
                throw new \InvalidArgumentException('Parameter values must be string, int, float, bool, or null');
            }
        }

        return new self($data['messageId'], $parameters);
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
