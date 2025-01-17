<?php

declare(strict_types=1);

namespace Profile\User\Application\UserPrivacyOperation\Service;

use Profile\User\DomainModel\Enum\UserId;
use Profile\User\DomainModel\Exception\ActivateUserException;
use Profile\User\DomainModel\Exception\DeactivateUserException;
use Profile\User\DomainModel\Exception\DeleteUserException;

interface UserPrivacyServiceInterface
{
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
