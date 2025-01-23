<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Types\Type;

abstract class AbstractType extends Type
{
    protected const ?string TYPE_NAME = null;
    protected const ?string CLASS_NAME = null;

    public function getName(): string
    {
        return $this->getDbIdTypeName();
    }

    protected function getDbIdTypeName(): string
    {
        if (is_null(static::TYPE_NAME)) {
            throw new \LogicException('Please overwrite constant \'MY_TYPE\', with proper value, in class '.static::class);
        }

        return static::TYPE_NAME;
    }

    protected function getIdClassName(): string
    {
        if (is_null(static::CLASS_NAME)) {
            throw new \LogicException('Please overwrite constant \'MY_UUID_CLASSNAME\', with proper value, in class '.static::class);
        }

        return static::CLASS_NAME;
    }
}
