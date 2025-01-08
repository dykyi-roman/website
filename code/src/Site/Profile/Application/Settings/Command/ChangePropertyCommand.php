<?php

declare(strict_types=1);

namespace Site\Profile\Application\Settings\Command;

use Site\Profile\DomainModel\ValueObject\Property;
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