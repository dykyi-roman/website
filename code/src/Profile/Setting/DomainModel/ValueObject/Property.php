<?php

declare(strict_types=1);

namespace Profile\Setting\DomainModel\ValueObject;

use Profile\Setting\DomainModel\Enum\PropertyCategory;
use Profile\Setting\DomainModel\Enum\PropertyName;

final readonly class Property implements \JsonSerializable
{
    public function __construct(
        public PropertyCategory $category,
        public PropertyName $name,
        public mixed $value,
    ) {
    }

    public function value(): string
    {
        if (is_bool($this->value)) {
            return $this->value ? '1' : '0';
        }

        if ($this->value instanceof \DateTimeInterface) {
            return $this->value->format('Y-m-d H:i:s');
        }

        if (is_scalar($this->value) || (is_object($this->value) && method_exists($this->value, '__toString'))) {
            return (string) $this->value;
        }

        if (is_array($this->value)) {
            return (string) json_encode($this->value());
        }

        if (is_null($this->value)) {
            return '';
        }

        throw new \InvalidArgumentException('Cannot convert value to string');
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'category' => $this->category->value,
            'name' => $this->name->value,
            'value' => $this->value(),
        ];
    }
}
