<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\DoctrineType;

use Doctrine\DBAL\Types\Type;

abstract class AbstractType extends Type
{
    protected const ?string ID_TYPE = null;
    protected const ?string ID_CLASSNAME = null;

    public function getName(): string
    {
        return $this->getDbIdTypeName();
    }

    protected function getDbIdTypeName(): string
    {
        if (is_null(static::ID_TYPE)) {
            throw new \LogicException(
                'Please overwrite constant \'MY_TYPE\', with proper value, in class ' . static::class
            );
        }

        return static::ID_TYPE;
    }

    protected function getIdClassName(): string
    {
        if (is_null(static::ID_CLASSNAME)) {
            throw new \LogicException(
                'Please overwrite constant \'MY_UUID_CLASSNAME\', with proper value, in class ' . static::class
            );
        }

        return static::ID_CLASSNAME;
    }

    protected function ensureEnum($value): void
    {
        if (!$value instanceof \UnitEnum) {
            throw new \InvalidArgumentException('Provided value is not a valid enum.');
        }
    }
}