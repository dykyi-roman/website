<?php

declare(strict_types=1);

namespace Profile\User\Application\GetCurrentUser\Service;

use Profile\User\DomainModel\Model\UserInterface;

interface UserFetcherInterface
{
    public function isLogin(): bool;

    /**
     * @throw AuthenticationException
     */
    public function fetch(): UserInterface;
}
