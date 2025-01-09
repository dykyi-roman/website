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

    public function toString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        if (is_null($value)) {
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
            'value' => $this->toString($this->value),
        ];
    }
}
