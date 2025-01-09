<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Command;

use Profile\Setting\DomainModel\ValueObject\Property;
use Site\User\DomainModel\Enum\UserId;

/**
 * @see ChangePropertyCommandHandler
 */
final readonly class ChangePropertyCommand
{
    public function __construct(
        public UserId $id,
        public Property $property,
    ) {
    }
}