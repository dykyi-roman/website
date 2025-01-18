<?php

declare(strict_types=1);

namespace Profile\User\Application\ChangeUserPassword\Service;

use Profile\User\DomainModel\Model\UserInterface;

interface PasswordChangeServiceInterface
{
    public function isValid(UserInterface $user, string $password): bool;

    public function change(UserInterface $user, string $password): void;
}
