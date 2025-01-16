<?php

declare(strict_types=1);

namespace Profile\User\DomainModel\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\ActivateUserException;
use Profile\User\DomainModel\Exception\DeactivateUserException;
use Profile\User\DomainModel\Exception\DeleteUserException;
use Profile\User\DomainModel\Exception\UserExistException;

interface UserServiceInterface
{
    /**
     * @throws UserExistException
     */
    public function update(
        UserId $userId,
        string $name,
        string $email,
        string $phone,
        ?string $avatar = null
    ): void;

    /**
     * @throws DeleteUserException
     */
    public function delete(UserId $userId): void;

    /**
     * @throws ActivateUserException
     */
    public function activate(UserId $userId): void;

    /**
     * @throws DeactivateUserException
     */
    public function deactivate(UserId $userId): void;
}