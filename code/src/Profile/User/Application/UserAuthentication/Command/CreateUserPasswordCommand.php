<?php

declare(strict_types=1);

namespace Profile\User\Application\UserAuthentication\Command;

use Profile\User\DomainModel\Enum\UserId;

/**
 * @see CreateUserPasswordCommandHandler
 */
final readonly class CreateUserPasswordCommand
{
    public function __construct(
        public UserId $userId,
        public string $password,
        public string $confirmationPassword,
    ) {
    }

    public function isEqual(): bool
    {
        return $this->password === $this->confirmationPassword;
    }
}
