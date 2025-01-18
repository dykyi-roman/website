<?php

declare(strict_types=1);

namespace Profile\User\Application\UserAuthentication\Command;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateUserPasswordHandler
{
    public function __invoke(CreateUserPassword $command)
    {
    }
}