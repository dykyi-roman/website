<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\ValueObject;

use Site\Profile\DomainModel\Enum\PropertyGroup;
use Site\Profile\DomainModel\Enum\PropertyName;
use Site\Profile\DomainModel\Enum\PropertyType;

final readonly class Property
{
    public function __construct(
        public PropertyGroup $group,
        public PropertyType $type,
        public PropertyName $name,
        public mixed $value,
    ) {
        $this->validateValue();
    }

    private function validateValue(): void
    {
        $isValid = match ($this->type) {
            PropertyType::STRING => is_string($this->value),
            PropertyType::INTEGER => is_int($this->value),
            PropertyType::BOOL => is_bool($this->value),
            PropertyType::DATE => $this->value instanceof \DateTimeInterface,
        };

        if (!$isValid) {
            throw new \InvalidArgumentException(sprintf('Property value must be of type %s, %s given', $this->type->value, get_debug_type($this->value)));
        }
    }
}
