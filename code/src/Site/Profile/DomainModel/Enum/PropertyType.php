<?php

declare(strict_types=1);

namespace Site\Profile\DomainModel\Enum;

enum PropertyType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case BOOL = 'bool';
    case DATE = 'date';

    public function convertToString(mixed $value): string
    {
        return match ($this) {
            self::STRING => is_string($value) ? $value : throw new \InvalidArgumentException('Invalid string value'),
            self::INTEGER => is_numeric($value) ? (string) $value : throw new \InvalidArgumentException('Invalid integer value'),
            self::BOOL => is_bool($value) ? ($value ? '1' : '0') : throw new \InvalidArgumentException('Invalid boolean value'),
            self::DATE => $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : throw new \InvalidArgumentException('Invalid date value'),
        };
    }
}
