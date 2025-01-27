<?php

declare(strict_types=1);

namespace Profile\UserStatus\Application\UpdateUserStatus\Command;

use Shared\DomainModel\ValueObject\UserId;

/**
 * @see UpdateUserStatusCommandHandler
 */
final readonly class UpdateUserStatusCommand
{
    public function __construct(
        public UserId $userId,
        public bool $isOnline,
        public ?\DateTimeImmutable $lastOnlineAt,
    ) {
    }
}