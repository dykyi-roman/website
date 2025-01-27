<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\UpdateUserStatus\Command;

/**
 * @see UpdateUserStatusCommandHandler
 */
final readonly class UpdateUserStatusCommand
{
    public function __construct(
        /** @var \Profile\UserStatus\DomainModel\Dto\UserUpdateStatus[] */
        public array $items,
    ) {
    }
}