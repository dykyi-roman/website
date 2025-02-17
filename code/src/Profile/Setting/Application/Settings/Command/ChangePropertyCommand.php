<?php

declare(strict_types=1);

namespace Profile\Setting\Application\Settings\Command;

use Profile\Setting\DomainModel\ValueObject\Property;
use Shared\DomainModel\ValueObject\UserId;

/**
 * @see ChangePropertyCommandHandler
 */
final readonly class ChangePropertyCommand
{
    public function __construct(
        public UserId $id,
        /**
         * @var Property[]
         */
        public array $properties,
    ) {
    }
}
