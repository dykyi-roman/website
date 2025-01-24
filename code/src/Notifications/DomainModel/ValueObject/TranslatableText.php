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
     * @param array{messageId: string, parameters?: string|array<string, string|int|float|bool|null>} $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['messageId']) || !is_string($data['messageId'])) {
            throw new \InvalidArgumentException('Invalid or missing messageId');
        }

        $parameters = [];
        if (isset($data['parameters'])) {
            if (is_string($data['parameters'])) {
                $decoded = json_decode($data['parameters'], true);
                if (!is_array($decoded)) {
                    throw new \InvalidArgumentException('Invalid JSON in parameters string');
                }
                $parameters = $decoded;
            } elseif (!is_array($data['parameters'])) {
                throw new \InvalidArgumentException('Parameters must be string or array');
            } else {
                $parameters = $data['parameters'];
            }

            // Validate parameter values and build a new array with validated values
            $validatedParameters = [];
            foreach ($parameters as $key => $value) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Parameter keys must be strings');
                }
                if (!is_null($value) && !is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
                    throw new \InvalidArgumentException('Parameter values must be string, int, float, bool, or null');
                }
                /* @var bool|float|int|string|null $value */
                $validatedParameters[$key] = $value;
            }
            $parameters = $validatedParameters;
        }

        /* @var array<string, string|int|float|bool|null> $parameters */
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
     * @return array{messageId: string, parameters: string}
     */
    public function jsonSerialize(): array
    {
        $encodedParams = json_encode($this->parameters);
        if (false === $encodedParams) {
            throw new \InvalidArgumentException('Failed to encode parameters to JSON');
        }

        return [
            'messageId' => $this->messageId,
            'parameters' => $encodedParams,
        ];
    }
}
