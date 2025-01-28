<?php

declare(strict_types=1);

namespace Shared\DomainModel\Services;

use Profile\User\DomainModel\Model\UserInterface;

interface UserFetcherInterface
{
    public function isLogin(): bool;

    /**
     * @throws \Shared\DomainModel\Exception\AuthenticationException
     */
    public function fetch(): UserInterface;
}
