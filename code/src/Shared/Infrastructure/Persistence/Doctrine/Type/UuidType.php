<?php

namespace Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;

abstract class UuidType extends AbstractType
{
    protected const ?string TYPE_NAME = null;
    protected const ?string CLASS_NAME = null;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof MySQLPlatform) {
            return 'BINARY(16)';
        }

        return 'uuid';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (empty($value)) {
            return null;
        }

        if ($platform instanceof MySQLPlatform) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException('Binary UUID value must be a string');
            }
            // Convert binary to UUID string
            $uuid = $this->binaryToUuid($value);

            return ($this->getIdClassName())::fromString($uuid);
        }

        return ($this->getIdClassName())::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if ($platform instanceof MySQLPlatform) {
            // Convert UUID to binary
            if (!is_object($value) || !method_exists($value, '__toString')) {
                throw new \InvalidArgumentException('UUID value must be convertible to string');
            }

            return $this->uuidToBinary($value->__toString());
        }

        return $value;
    }

    private function uuidToBinary(string $uuid): string
    {
        // Remove hyphens and convert to binary
        $uuid = str_replace('-', '', $uuid);

        $binary = hex2bin($uuid);
        if (false === $binary) {
            throw new \InvalidArgumentException('Invalid UUID hex string');
        }

        return $binary;
    }

    private function binaryToUuid(string $binary): string
    {
        // Convert binary to UUID string with hyphens
        $hex = bin2hex($binary);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }
}
