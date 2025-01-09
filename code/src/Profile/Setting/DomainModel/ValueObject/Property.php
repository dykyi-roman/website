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

        return (string) $value;
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
       return [
           'category' => $this->category->value,
           'name' => $this->name->value,
           'value' => (string) $this->value,
       ];
    }
}
