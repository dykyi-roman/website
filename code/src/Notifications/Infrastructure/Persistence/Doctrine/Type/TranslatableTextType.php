<?php

declare(strict_types=1);

namespace Notifications\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Notifications\DomainModel\ValueObject\TranslatableText;

class TranslatableTextType extends Type
{
    public const string NAME = 'translatable_text';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof TranslatableText) {
            throw new \InvalidArgumentException(
                sprintf('Expected instance of %s, got %s instead.', TranslatableText::class, get_debug_type($value))
            );
        }

        return json_encode($value->jsonSerialize());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TranslatableText
    {
        if ($value === null || $value === '') {
            return null;
        }

        $data = json_decode($value, true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON data for TranslatableText.');
        }

        return TranslatableText::fromArray($data);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
